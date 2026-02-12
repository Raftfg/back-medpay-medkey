<?php

namespace App\Core\Services;

use App\Core\Models\Hospital;
use App\Core\Models\HospitalModule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service de Gestion des Modules
 * 
 * Gère l'activation, la désactivation et la vérification des modules pour chaque tenant.
 * 
 * @package App\Core\Services
 */
class ModuleService
{
    /**
     * Cache key prefix pour les modules activés
     */
    protected const CACHE_PREFIX = 'hospital_modules:';

    /**
     * Durée du cache (en secondes)
     */
    protected const CACHE_TTL = 3600; // 1 heure

    /**
     * Active un module pour un hôpital
     * 
     * @param Hospital $hospital
     * @param string $moduleName Nom du module (ex: "Acl", "Patient", "Stock")
     * @param array $config Configuration optionnelle du module
     * @param int|null $adminId ID de l'administrateur qui active le module
     * @return HospitalModule
     * @throws Exception
     */
    public function enableModule(Hospital $hospital, string $moduleName, array $config = [], ?int $adminId = null): HospitalModule
    {
        try {
            $module = HospitalModule::updateOrCreate(
                [
                    'hospital_id' => $hospital->id,
                    'module_name' => $moduleName,
                ],
                [
                    'is_enabled' => true,
                    'config' => $config,
                    'enabled_at' => now(),
                    'disabled_at' => null,
                    'enabled_by' => $adminId,
                ]
            );

            // Invalider le cache
            $this->clearCache($hospital);

            Log::info("Module activé : {$moduleName} pour l'hôpital {$hospital->id}");

            return $module;

        } catch (Exception $e) {
            Log::error("Erreur lors de l'activation du module {$moduleName} pour l'hôpital {$hospital->id}: " . $e->getMessage());
            throw new Exception("Impossible d'activer le module '{$moduleName}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Désactive un module pour un hôpital
     * 
     * @param Hospital $hospital
     * @param string $moduleName Nom du module
     * @return bool
     * @throws Exception
     */
    public function disableModule(Hospital $hospital, string $moduleName): bool
    {
        try {
            $module = HospitalModule::where('hospital_id', $hospital->id)
                ->where('module_name', $moduleName)
                ->first();

            if (!$module) {
                throw new Exception("Le module '{$moduleName}' n'est pas enregistré pour cet hôpital.");
            }

            $module->disable();

            // Invalider le cache
            $this->clearCache($hospital);

            Log::info("Module désactivé : {$moduleName} pour l'hôpital {$hospital->id}");

            return true;

        } catch (Exception $e) {
            Log::error("Erreur lors de la désactivation du module {$moduleName} pour l'hôpital {$hospital->id}: " . $e->getMessage());
            throw new Exception("Impossible de désactiver le module '{$moduleName}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Vérifie si un module est activé pour un hôpital
     * 
     * @param Hospital $hospital
     * @param string $moduleName Nom du module
     * @return bool
     */
    public function isModuleEnabled(Hospital $hospital, string $moduleName): bool
    {
        $cacheKey = $this->getCacheKey($hospital);

        // Récupérer depuis le cache
        $enabledModules = Cache::get($cacheKey);

        if ($enabledModules === null) {
            // Charger depuis la base de données
            $enabledModules = HospitalModule::where('hospital_id', $hospital->id)
                ->where('is_enabled', true)
                ->pluck('module_name')
                ->toArray();

            // Mettre en cache
            Cache::put($cacheKey, $enabledModules, self::CACHE_TTL);
        }

        return in_array($moduleName, $enabledModules);
    }

    /**
     * Récupère tous les modules activés pour un hôpital
     * 
     * @param Hospital $hospital
     * @return array Liste des noms de modules activés
     */
    public function getEnabledModules(Hospital $hospital): array
    {
        $cacheKey = $this->getCacheKey($hospital);

        // Récupérer depuis le cache
        $enabledModules = Cache::get($cacheKey);

        if ($enabledModules === null) {
            // Charger depuis la base de données
            $enabledModules = HospitalModule::where('hospital_id', $hospital->id)
                ->where('is_enabled', true)
                ->pluck('module_name')
                ->toArray();

            // Mettre en cache
            Cache::put($cacheKey, $enabledModules, self::CACHE_TTL);
        }

        return $enabledModules;
    }

    /**
     * Récupère tous les modules disponibles dans l'application
     * 
     * @return array Liste des noms de modules disponibles
     */
    public function getAvailableModules(): array
    {
        $modulesPath = base_path('Modules');
        
        if (!is_dir($modulesPath)) {
            return [];
        }

        $modules = [];
        $directories = array_filter(glob($modulesPath . '/*'), 'is_dir');

        foreach ($directories as $dir) {
            $moduleName = basename($dir);
            // Vérifier que c'est un module valide (a un module.json ou un Providers/)
            if (file_exists($dir . '/module.json') || is_dir($dir . '/Providers')) {
                $modules[] = $moduleName;
            }
        }

        return $modules;
    }

    /**
     * Récupère le statut de tous les modules pour un hôpital
     * 
     * @param Hospital $hospital
     * @return array Tableau associatif [module_name => is_enabled]
     */
    public function getModulesStatus(Hospital $hospital): array
    {
        $availableModules = $this->getAvailableModules();
        $enabledModules = $this->getEnabledModules($hospital);

        $status = [];
        foreach ($availableModules as $module) {
            $status[$module] = in_array($module, $enabledModules);
        }

        return $status;
    }

    /**
     * Active plusieurs modules en une fois
     * 
     * @param Hospital $hospital
     * @param array $moduleNames Liste des noms de modules
     * @param int|null $adminId
     * @return array Liste des modules activés
     */
    public function enableModules(Hospital $hospital, array $moduleNames, ?int $adminId = null): array
    {
        $activated = [];

        foreach ($moduleNames as $moduleName) {
            try {
                $this->enableModule($hospital, trim($moduleName), [], $adminId);
                $activated[] = $moduleName;
            } catch (Exception $e) {
                Log::warning("Impossible d'activer le module {$moduleName}: " . $e->getMessage());
            }
        }

        return $activated;
    }

    /**
     * Désactive plusieurs modules en une fois
     * 
     * @param Hospital $hospital
     * @param array $moduleNames Liste des noms de modules
     * @return array Liste des modules désactivés
     */
    public function disableModules(Hospital $hospital, array $moduleNames): array
    {
        $deactivated = [];

        foreach ($moduleNames as $moduleName) {
            try {
                $this->disableModule($hospital, trim($moduleName));
                $deactivated[] = $moduleName;
            } catch (Exception $e) {
                Log::warning("Impossible de désactiver le module {$moduleName}: " . $e->getMessage());
            }
        }

        return $deactivated;
    }

    /**
     * Met à jour la configuration d'un module
     * 
     * @param Hospital $hospital
     * @param string $moduleName
     * @param array $config
     * @return HospitalModule
     * @throws Exception
     */
    public function updateModuleConfig(Hospital $hospital, string $moduleName, array $config): HospitalModule
    {
        $module = HospitalModule::where('hospital_id', $hospital->id)
            ->where('module_name', $moduleName)
            ->first();

        if (!$module) {
            throw new Exception("Le module '{$moduleName}' n'est pas enregistré pour cet hôpital.");
        }

        $module->update(['config' => $config]);
        $this->clearCache($hospital);

        return $module;
    }

    /**
     * Récupère la configuration d'un module
     * 
     * @param Hospital $hospital
     * @param string $moduleName
     * @return array
     */
    public function getModuleConfig(Hospital $hospital, string $moduleName): array
    {
        $module = HospitalModule::where('hospital_id', $hospital->id)
            ->where('module_name', $moduleName)
            ->first();

        return $module ? ($module->config ?? []) : [];
    }

    /**
     * Invalide le cache des modules pour un hôpital
     * 
     * @param Hospital $hospital
     * @return void
     */
    protected function clearCache(Hospital $hospital): void
    {
        $cacheKey = $this->getCacheKey($hospital);
        Cache::forget($cacheKey);
    }

    /**
     * Génère la clé de cache pour un hôpital
     * 
     * @param Hospital $hospital
     * @return string
     */
    protected function getCacheKey(Hospital $hospital): string
    {
        return self::CACHE_PREFIX . $hospital->id;
    }
}
