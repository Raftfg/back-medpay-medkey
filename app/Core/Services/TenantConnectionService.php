<?php

namespace App\Core\Services;

use App\Core\Models\Hospital;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

/**
 * Service TenantConnectionService
 * 
 * Gère les connexions dynamiques aux bases de données des tenants (hôpitaux).
 * Configure automatiquement la connexion 'tenant' en fonction de l'hôpital identifié.
 * 
 * @package App\Core\Services
 */
class TenantConnectionService
{
    /**
     * Nom de la connexion tenant
     *
     * @var string
     */
    protected string $connectionName = 'tenant';

    /**
     * Hôpital courant connecté
     *
     * @var Hospital|null
     */
    protected ?Hospital $currentHospital = null;

    /**
     * Connecte à la base de données d'un hôpital (tenant)
     *
     * @param  Hospital  $hospital
     * @return void
     * @throws \Exception
     */
    public function connect(Hospital $hospital): void
    {
        // Vérifier que l'hôpital est actif
        if (!$hospital->isActive() && !$hospital->isProvisioning()) {
            throw new \Exception("Cannot connect to inactive hospital: {$hospital->name}");
        }

        // Si déjà connecté au même hôpital, ne rien faire
        if ($this->currentHospital && $this->currentHospital->id === $hospital->id) {
            return;
        }

        // Récupérer la configuration de la base de données
        $config = $hospital->getDatabaseConfig();

        // ÉVITER LES DÉLAIS DNS (localhost -> 127.0.0.1 sur Windows)
        if ($config['host'] === 'localhost') {
            $config['host'] = '127.0.0.1';
        }

        // Vérifier si la configuration a réellement changé pour éviter de purger les connexions pour rien
        $oldConfig = Config::get("database.connections.{$this->connectionName}");
        $isSameConfig = $oldConfig && 
                        ($oldConfig['database'] === $config['database']) && 
                        ($oldConfig['host'] === $config['host']) &&
                        ($oldConfig['port'] === ($config['port'] ?? '3306'));

        if ($isSameConfig && $this->currentHospital && $this->currentHospital->id === $hospital->id) {
            return;
        }

        // Mettre à jour la configuration de la connexion tenant
        Config::set("database.connections.{$this->connectionName}", $config);

        // Synchroniser également la connexion par défaut (mysql) pour que tous les modèles 
        // et packages (comme Passport) utilisent la base du tenant par défaut.
        Config::set("database.connections.mysql.host", $config['host']);
        Config::set("database.connections.mysql.database", $config['database']);
        if (isset($config['username'])) Config::set("database.connections.mysql.username", $config['username']);
        if (isset($config['password'])) Config::set("database.connections.mysql.password", $config['password']);
        if (isset($config['port'])) Config::set("database.connections.mysql.port", $config['port']);

        // Purger UNIQUEMENT si nécessaire
        DB::purge($this->connectionName);
        DB::purge('mysql');

        // Tester la connexion
        try {
            DB::connection($this->connectionName)->getPdo();
            
            // Enregistrer l'hôpital courant
            $this->currentHospital = $hospital;
            
            // Stocker dans le container de l'application
            app()->instance('current.hospital', $hospital);
            app()->instance('current.hospital.id', $hospital->id);
            
            // Mettre en cache pour améliorer les performances
            if (config('tenant.cache.enabled', true)) {
                $cacheKey = $this->getCacheKey($hospital->id);
                Cache::put($cacheKey, $hospital, config('tenant.cache.ttl', 3600));
            }

            Log::info("Connected to tenant database", [
                'hospital_id' => $hospital->id,
                'hospital_name' => $hospital->name,
                'database' => $config['database'],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to connect to tenant database", [
                'hospital_id' => $hospital->id,
                'hospital_name' => $hospital->name,
                'database' => $config['database'],
                'error' => $e->getMessage(),
            ]);

            throw new \Exception("Failed to connect to tenant database: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Alias pour connect() - Rétrocompatibilité
     *
     * @param  Hospital  $hospital
     * @return void
     * @throws \Exception
     */
    public function setTenantConnection(Hospital $hospital): void
    {
        Log::warning("Exécution de la méthode dépréciée setTenantConnection via " . debug_backtrace()[1]['function'] ?? 'unknown');
        $this->connect($hospital);
    }

    /**
     * Déconnecte de la base de données tenant courante
     *
     * @return void
     */
    public function disconnect(): void
    {
        if ($this->currentHospital) {
            $hospitalId = $this->currentHospital->id;
            
            // Fermer la connexion
            DB::disconnect($this->connectionName);
            
            // Réinitialiser la configuration
            Config::set("database.connections.{$this->connectionName}.database", null);
            
            // Nettoyer le cache
            if (config('tenant.cache.enabled', true)) {
                $cacheKey = $this->getCacheKey($hospitalId);
                Cache::forget($cacheKey);
            }
            
            // Nettoyer les instances dans le container
            app()->forgetInstance('current.hospital');
            app()->forgetInstance('current.hospital.id');
            
            Log::info("Disconnected from tenant database", [
                'hospital_id' => $hospitalId,
            ]);
        }

        $this->currentHospital = null;
    }

    /**
     * Récupère la connexion tenant courante
     *
     * @return \Illuminate\Database\Connection|null
     */
    public function getCurrentConnection(): ?\Illuminate\Database\Connection
    {
        if (!$this->currentHospital) {
            return null;
        }

        try {
            return DB::connection($this->connectionName);
        } catch (\Exception $e) {
            Log::error("Failed to get current tenant connection", [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Récupère l'hôpital courant connecté
     * 
     * Priorité :
     * 1. L'hôpital actuellement connecté via ce service
     * 2. L'hôpital de l'utilisateur authentifié (si disponible)
     * 3. L'hôpital depuis le container de l'application
     *
     * @return Hospital|null
     */
    public function getCurrentHospital(): ?Hospital
    {
        // Priorité 1 : Hôpital actuellement connecté via ce service
        if ($this->currentHospital) {
            return $this->currentHospital;
        }

        // Priorité 2 : Hôpital de l'utilisateur authentifié (source de vérité pour le multi-tenancy)
        if (Auth::check()) {
            $user = Auth::user();
            if ($user && isset($user->hospital_id) && $user->hospital_id) {
                $hospitalId = $user->hospital_id;
                
                // Vérifier le cache d'abord
                $cachedHospital = $this->getHospitalFromCache($hospitalId);
                if ($cachedHospital) {
                    return $cachedHospital;
                }
                
                // Récupérer depuis la base CORE (Hospital est dans la base CORE)
                try {
                    $hospital = Hospital::on('core')->find($hospitalId);
                    if ($hospital) {
                        // Mettre en cache
                        if (config('tenant.cache.enabled', true)) {
                            $cacheKey = $this->getCacheKey($hospitalId);
                            Cache::put($cacheKey, $hospital, config('tenant.cache.ttl', 3600));
                        }
                        return $hospital;
                    }
                } catch (\Exception $e) {
                    Log::warning("Failed to get hospital from user hospital_id", [
                        'hospital_id' => $hospitalId,
                        'user_id' => $user->id ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // Priorité 3 : Hôpital depuis le container de l'application
        if (app()->bound('current.hospital')) {
            return app('current.hospital');
        }

        return null;
    }

    /**
     * Vérifie si une connexion tenant est active
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->currentHospital !== null;
    }

    /**
     * Vérifie si la connexion à un hôpital est valide
     *
     * @param  Hospital  $hospital
     * @return bool
     */
    public function testConnection(Hospital $hospital): bool
    {
        try {
            $config = $hospital->getDatabaseConfig();
            
            // Créer une connexion temporaire pour tester
            $tempConnection = 'temp_' . $hospital->id;
            Config::set("database.connections.{$tempConnection}", $config);
            
            DB::connection($tempConnection)->getPdo();
            
            // Nettoyer
            DB::disconnect($tempConnection);
            Config::set("database.connections.{$tempConnection}", null);
            
            return true;
        } catch (\Exception $e) {
            Log::warning("Connection test failed for hospital", [
                'hospital_id' => $hospital->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Récupère la clé de cache pour un hôpital
     *
     * @param  int  $hospitalId
     * @return string
     */
    protected function getCacheKey(int $hospitalId): string
    {
        $prefix = config('tenant.cache.prefix', 'tenant_');
        return $prefix . $hospitalId;
    }

    /**
     * Récupère l'hôpital depuis le cache
     *
     * @param  int  $hospitalId
     * @return Hospital|null
     */
    public function getHospitalFromCache(int $hospitalId): ?Hospital
    {
        if (!config('tenant.cache.enabled', true)) {
            return null;
        }

        $cacheKey = $this->getCacheKey($hospitalId);
        return Cache::get($cacheKey);
    }

    /**
     * Réinitialise le service (utile pour les tests)
     *
     * @return void
     */
    public function reset(): void
    {
        $this->disconnect();
        $this->currentHospital = null;
    }
}
