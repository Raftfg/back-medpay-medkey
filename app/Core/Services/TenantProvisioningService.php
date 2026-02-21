<?php

namespace App\Core\Services;

use App\Core\Models\Hospital;
use App\Core\Models\HospitalModule;
use App\Core\Services\TenantConnectionService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\ClientRepository;
use Exception;

/**
 * Service de Provisioning des Tenants
 * 
 * Gère la création complète d'un nouveau tenant (hôpital) :
 * - Création de la base de données
 * - Exécution des migrations
 * - Activation des modules
 * - Exécution des seeders
 * 
 * @package App\Core\Services
 */
class TenantProvisioningService
{
    protected TenantConnectionService $tenantConnectionService;

    public function __construct(TenantConnectionService $tenantConnectionService)
    {
        $this->tenantConnectionService = $tenantConnectionService;
    }

    /**
     * Provisionne un hôpital (tenant) complet
     * 
     * Crée la base de données, exécute les migrations, active les modules et exécute les seeders.
     * 
     * @param Hospital $hospital
     * @param array $options Options de provisioning
     * @return array Résultat du provisioning
     * @throws Exception
     */
    public function provision(Hospital $hospital, array $options = []): array
    {
        $options = array_merge([
            'create_database' => true,
            'run_migrations' => true,
            'activate_default_modules' => true,
            'selected_modules' => null,
            'run_seeders' => config('tenant.provisioning.auto_seed', false),
            'force' => false,
            // Mode idempotent : ne pas lever d'erreur si la base existe déjà
            'skip_if_exists' => false,
        ], $options);

        $results = [
            'database_created' => false,
            'migrations_executed' => false,
            'modules_activated' => false,
            'seeders_executed' => false,
            'errors' => [],
        ];

        try {
            // 1. Créer la base de données
            if ($options['create_database']) {
                $this->createDatabase($hospital, $options['force'], $options['skip_if_exists']);
                $results['database_created'] = true;
            }

            // 2. Exécuter les migrations
            if ($options['run_migrations']) {
                $this->runMigrations($hospital);
                $this->ensureTenantOauthIsReady($hospital);
                $results['migrations_executed'] = true;
                $results['oauth_ready'] = true;
            }

            // 3. Activer les modules par défaut
            if ($options['activate_default_modules']) {
                if (is_array($options['selected_modules']) && !empty($options['selected_modules'])) {
                    $modules = array_values(array_unique(array_filter(array_map('trim', $options['selected_modules']))));
                } else {
                    $defaultModules = config('tenant.provisioning.default_modules', 'Acl,Administration,Patient,Payment');
                    $modules = array_map('trim', explode(',', $defaultModules));
                }
                $this->activateModules($hospital, $modules);
                $results['modules_activated'] = true;
                $results['modules'] = $modules;
            }

            // 4. Exécuter les seeders
            if ($options['run_seeders']) {
                $this->seed($hospital);
                $results['seeders_executed'] = true;
            }

            // 5. Mettre à jour le statut de l'hôpital
            if ($hospital->isProvisioning()) {
                $hospital->update([
                    'status' => 'active',
                    'provisioned_at' => now(),
                ]);
            }

            return $results;

        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            Log::error("Erreur lors du provisioning de l'hôpital {$hospital->id}: " . $e->getMessage(), [
                'hospital_id' => $hospital->id,
                'hospital_name' => $hospital->name,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Crée la base de données pour un hôpital
     * 
     * @param Hospital $hospital
     * @param bool $force Forcer la création même si la base existe
     * @return void
     * @throws Exception
     */
    public function createDatabase(Hospital $hospital, bool $force = false, bool $skipIfExists = false): void
    {
        $databaseName = $hospital->database_name;
        $charset = config('database.connections.mysql.charset', 'utf8mb4');
        $collation = config('database.connections.mysql.collation', 'utf8mb4_unicode_ci');

        try {
            // Utiliser la connexion par défaut pour créer la base
            $defaultConnection = DB::connection();
            
            // Vérifier si la base existe déjà
            $exists = $defaultConnection->select(
                "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?",
                [$databaseName]
            );

            if (!empty($exists)) {
                if ($skipIfExists && !$force) {
                    // Mode idempotent : la base existe déjà, on loggue et on sort sans erreur
                    Log::info("Base de données {$databaseName} déjà existante - création ignorée (mode idempotent).");
                    return;
                }

                if ($force) {
                    Log::warning("Base de données {$databaseName} existe déjà, suppression forcée...");
                    $defaultConnection->statement("DROP DATABASE IF EXISTS `{$databaseName}`");
                    $defaultConnection->statement("CREATE DATABASE `{$databaseName}` CHARACTER SET {$charset} COLLATE {$collation}");
                } else {
                    throw new Exception("La base de données '{$databaseName}' existe déjà. Utilisez --force pour la recréer.");
                }
            } else {
                $defaultConnection->statement("CREATE DATABASE `{$databaseName}` CHARACTER SET {$charset} COLLATE {$collation}");
            }

            Log::info("Base de données créée : {$databaseName}");

        } catch (Exception $e) {
            Log::error("Impossible de créer la base de données '{$databaseName}': " . $e->getMessage());
            throw new Exception("Impossible de créer la base de données '{$databaseName}': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Exécute les migrations pour un hôpital
     * 
     * @param Hospital $hospital
     * @param string|null $path Chemin spécifique vers les migrations (optionnel)
     * @return void
     * @throws Exception
     */
    public function runMigrations(Hospital $hospital, ?string $path = null): void
    {
        try {
            // Connecter à la base tenant
            $this->tenantConnectionService->connect($hospital);

            // Exécuter les migrations principales
            $migrationParams = [
                '--database' => 'tenant',
                '--force' => true,
            ];

            if ($path) {
                $migrationParams['--path'] = $path;
                Artisan::call('migrate', $migrationParams);
            } else {
                // Migrations principales
                $migrationParams['--path'] = 'database/migrations';
                Artisan::call('migrate', $migrationParams);

                // Exécuter les migrations des modules
                $modulesPath = base_path('Modules');
                if (is_dir($modulesPath)) {
                    $modules = array_filter(glob($modulesPath . '/*'), 'is_dir');
                    foreach ($modules as $modulePath) {
                        $moduleName = basename($modulePath);
                        $migrationsPath = $modulePath . '/Database/Migrations';

                        if (is_dir($migrationsPath)) {
                            $migrationParams['--path'] = "Modules/{$moduleName}/Database/Migrations";
                            Artisan::call('migrate', $migrationParams);
                        }
                    }
                }
            }

            Log::info("Migrations exécutées pour l'hôpital {$hospital->id}");

        } catch (Exception $e) {
            Log::error("Erreur lors de l'exécution des migrations pour l'hôpital {$hospital->id}: " . $e->getMessage());
            throw new Exception("Erreur lors de l'exécution des migrations : " . $e->getMessage(), 0, $e);
        } finally {
            $this->tenantConnectionService->disconnect();
        }
    }

    /**
     * Garantit la disponibilité OAuth Passport dans la base tenant.
     *
     * - Vérifie la présence des tables oauth_*
     * - Relance les migrations OAuth ACL si nécessaire
     * - Crée un personal access client si absent
     *
     * @throws Exception
     */
    private function ensureTenantOauthIsReady(Hospital $hospital): void
    {
        $oauthMigrationPaths = [
            'Modules/Acl/Database/Migrations/2016_06_01_000001_create_oauth_auth_codes_table.php',
            'Modules/Acl/Database/Migrations/2016_06_01_000002_create_oauth_access_tokens_table.php',
            'Modules/Acl/Database/Migrations/2016_06_01_000003_create_oauth_refresh_tokens_table.php',
            'Modules/Acl/Database/Migrations/2016_06_01_000004_create_oauth_clients_table.php',
            'Modules/Acl/Database/Migrations/2016_06_01_000005_create_oauth_personal_access_clients_table.php',
        ];

        $requiredTables = [
            'oauth_auth_codes',
            'oauth_access_tokens',
            'oauth_refresh_tokens',
            'oauth_clients',
            'oauth_personal_access_clients',
        ];

        try {
            $this->tenantConnectionService->connect($hospital);

            $schema = DB::connection('tenant')->getSchemaBuilder();
            $missing = array_values(array_filter(
                $requiredTables,
                fn (string $table): bool => !$schema->hasTable($table)
            ));

            if (!empty($missing)) {
                foreach ($oauthMigrationPaths as $migrationPath) {
                    Artisan::call('migrate', [
                        '--database' => 'tenant',
                        '--path' => $migrationPath,
                        '--force' => true,
                    ]);
                }

                // Revalidation stricte après tentative de remédiation
                $schema = DB::connection('tenant')->getSchemaBuilder();
                $missing = array_values(array_filter(
                    $requiredTables,
                    fn (string $table): bool => !$schema->hasTable($table)
                ));

                if (!empty($missing)) {
                    throw new Exception(
                        'Provisioning incomplet: tables OAuth manquantes dans tenant [' . implode(', ', $missing) . '].'
                    );
                }
            }

            $hasPersonalAccessClient = DB::connection('tenant')
                ->table('oauth_personal_access_clients')
                ->exists();

            if (!$hasPersonalAccessClient) {
                /** @var ClientRepository $clientRepository */
                $clientRepository = app(ClientRepository::class);
                $clientRepository->createPersonalAccessClient(
                    null,
                    "{$hospital->name} Personal Access Client",
                    config('app.url')
                );
            }
        } catch (Exception $e) {
            Log::error("Erreur d'initialisation OAuth tenant pour l'hôpital {$hospital->id}: " . $e->getMessage());
            throw new Exception("OAuth tenant non prêt : " . $e->getMessage(), 0, $e);
        } finally {
            $this->tenantConnectionService->disconnect();
        }
    }

    /**
     * Exécute les migrations pour tous les tenants actifs
     * 
     * Cette méthode est essentielle : toute nouvelle migration doit être appliquée
     * à TOUS les tenants, pas seulement au tenant en cours.
     * 
     * @param string|null $path Chemin spécifique vers les migrations (optionnel)
     * @param callable|null $progressCallback Callback pour suivre la progression (optionnel)
     * @return array Résultats par tenant ['hospital_id' => ['success' => bool, 'error' => string|null]]
     * @throws Exception
     */
    public function runMigrationsForAllTenants(?string $path = null, ?callable $progressCallback = null): array
    {
        $results = [];
        
        // Récupérer tous les tenants actifs
        $hospitals = Hospital::whereIn('status', ['active', 'provisioning'])->get();
        
        if ($hospitals->isEmpty()) {
            Log::warning("Aucun tenant actif trouvé pour exécuter les migrations");
            return $results;
        }

        Log::info("Début de l'exécution des migrations pour tous les tenants", [
            'total_tenants' => $hospitals->count(),
        ]);

        foreach ($hospitals as $hospital) {
            $hospitalId = $hospital->id;
            $results[$hospitalId] = [
                'hospital_id' => $hospitalId,
                'hospital_name' => $hospital->name,
                'database_name' => $hospital->database_name,
                'success' => false,
                'error' => null,
            ];

            try {
                if ($progressCallback) {
                    $progressCallback($hospital, 'start');
                }

                $this->runMigrations($hospital, $path);
                
                $results[$hospitalId]['success'] = true;
                
                if ($progressCallback) {
                    $progressCallback($hospital, 'success');
                }

                Log::info("Migrations exécutées avec succès pour le tenant", [
                    'hospital_id' => $hospitalId,
                    'hospital_name' => $hospital->name,
                ]);

            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
                
                // Ignorer les erreurs de base de données inexistante (c'est normal pour les nouveaux tenants)
                $isDatabaseNotFound = strpos($errorMessage, 'Unknown database') !== false || 
                                     strpos($errorMessage, "doesn't exist") !== false ||
                                     strpos($errorMessage, 'Base table or view already exists') !== false;
                
                if ($isDatabaseNotFound) {
                    $results[$hospitalId]['success'] = true; // Marquer comme succès car ce n'est pas une vraie erreur
                    $results[$hospitalId]['error'] = 'Base de données non créée ou table déjà existante (ignoré)';
                    $results[$hospitalId]['skipped'] = true;
                    
                    if ($progressCallback) {
                        $progressCallback($hospital, 'skipped', $errorMessage);
                    }
                    
                    Log::info("Base de données non trouvée pour le tenant (ignoré)", [
                        'hospital_id' => $hospitalId,
                        'hospital_name' => $hospital->name,
                    ]);
                } else {
                    $results[$hospitalId]['error'] = $errorMessage;
                    
                    if ($progressCallback) {
                        $progressCallback($hospital, 'error', $errorMessage);
                    }

                    Log::error("Erreur lors de l'exécution des migrations pour le tenant", [
                        'hospital_id' => $hospitalId,
                        'hospital_name' => $hospital->name,
                        'error' => $errorMessage,
                    ]);
                }
            }
        }

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $errorCount = count($results) - $successCount;

        Log::info("Fin de l'exécution des migrations pour tous les tenants", [
            'total' => count($results),
            'success' => $successCount,
            'errors' => $errorCount,
        ]);

        return $results;
    }

    /**
     * Active des modules pour un hôpital
     * 
     * @param Hospital $hospital
     * @param array $modules Liste des noms de modules à activer
     * @return void
     */
    public function activateModules(Hospital $hospital, array $modules): void
    {
        foreach ($modules as $moduleName) {
            $moduleName = trim($moduleName);
            if (empty($moduleName)) {
                continue;
            }

            HospitalModule::updateOrCreate(
                [
                    'hospital_id' => $hospital->id,
                    'module_name' => $moduleName,
                ],
                [
                    'is_enabled' => true,
                ]
            );
        }

        Log::info("Modules activés pour l'hôpital {$hospital->id}: " . implode(', ', $modules));
    }

    /**
     * Désactive des modules pour un hôpital
     * 
     * @param Hospital $hospital
     * @param array $modules Liste des noms de modules à désactiver
     * @return void
     */
    public function deactivateModules(Hospital $hospital, array $modules): void
    {
        foreach ($modules as $moduleName) {
            $moduleName = trim($moduleName);
            if (empty($moduleName)) {
                continue;
            }

            HospitalModule::where('hospital_id', $hospital->id)
                ->where('module_name', $moduleName)
                ->update(['is_enabled' => false]);
        }

        Log::info("Modules désactivés pour l'hôpital {$hospital->id}: " . implode(', ', $modules));
    }

    /**
     * Exécute les seeders pour un hôpital
     * 
     * @param Hospital $hospital
     * @param string|null $seederClass Classe de seeder spécifique (optionnel)
     * @param \Illuminate\Console\Command|null $command Commande pour les messages (optionnel)
     * @return void
     * @throws Exception
     */
    public function seed(Hospital $hospital, ?string $seederClass = null, ?\Illuminate\Console\Command $command = null): void
    {
        try {
            // Connecter à la base tenant
            $this->tenantConnectionService->connect($hospital);

            // Purger les connexions pour éviter les conflits
            DB::purge('tenant');
            DB::purge('mysql');

            $params = [
                '--database' => 'tenant',
                '--force' => true,
            ];

            if ($seederClass) {
                // Nettoyer les backslashes doubles pour le format Laravel
                $cleanSeederClass = str_replace('\\\\', '\\', $seederClass);
                
                // Vérifier que la classe existe
                if (!class_exists($cleanSeederClass)) {
                    throw new Exception("La classe de seeder '{$cleanSeederClass}' n'existe pas.");
                }

                // Exécuter le seeder directement pour éviter les problèmes de chargement de classe
                // entre les tenants
                $seeder = new $cleanSeederClass();
                if ($command && method_exists($seeder, 'setCommand')) {
                    $seeder->setCommand($command);
                }
                $seeder->run();
            } else {
                Artisan::call('db:seed', $params);
            }

            Log::info("Seeders exécutés pour l'hôpital {$hospital->id}");

        } catch (Exception $e) {
            Log::error("Erreur lors de l'exécution des seeders pour l'hôpital {$hospital->id}: " . $e->getMessage());
            throw new Exception("Erreur lors de l'exécution des seeders : " . $e->getMessage(), 0, $e);
        } finally {
            $this->tenantConnectionService->disconnect();
        }
    }

    /**
     * Vérifie si un hôpital est complètement provisionné
     * 
     * @param Hospital $hospital
     * @return bool
     */
    public function isProvisioned(Hospital $hospital): bool
    {
        // Vérifier que la base de données existe
        try {
            $defaultConnection = DB::connection();
            $exists = $defaultConnection->select(
                "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?",
                [$hospital->database_name]
            );

            if (empty($exists)) {
                return false;
            }

            // Vérifier que les migrations ont été exécutées
            $this->tenantConnectionService->connect($hospital);
            $tenantConnection = $this->tenantConnectionService->getCurrentConnection();
            
            if (!$tenantConnection->getSchemaBuilder()->hasTable('migrations')) {
                $this->tenantConnectionService->disconnect();
                return false;
            }

            $migrationsCount = $tenantConnection->table('migrations')->count();
            $this->tenantConnectionService->disconnect();

            // Considérer comme provisionné si au moins quelques migrations ont été exécutées
            return $migrationsCount > 0;

        } catch (Exception $e) {
            Log::warning("Erreur lors de la vérification du provisioning pour l'hôpital {$hospital->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtient le statut de provisioning d'un hôpital
     * 
     * @param Hospital $hospital
     * @return array
     */
    public function getProvisioningStatus(Hospital $hospital): array
    {
        $status = [
            'database_exists' => false,
            'migrations_count' => 0,
            'modules_count' => 0,
            'is_provisioned' => false,
        ];

        try {
            // Vérifier la base de données
            $defaultConnection = DB::connection();
            $exists = $defaultConnection->select(
                "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?",
                [$hospital->database_name]
            );
            $status['database_exists'] = !empty($exists);

            if ($status['database_exists']) {
                // Vérifier les migrations
                $this->tenantConnectionService->connect($hospital);
                $tenantConnection = $this->tenantConnectionService->getCurrentConnection();
                
                if ($tenantConnection->getSchemaBuilder()->hasTable('migrations')) {
                    $status['migrations_count'] = $tenantConnection->table('migrations')->count();
                }
                
                $this->tenantConnectionService->disconnect();
            }

            // Compter les modules activés
            $status['modules_count'] = HospitalModule::where('hospital_id', $hospital->id)
                ->where('is_enabled', true)
                ->count();

            $status['is_provisioned'] = $this->isProvisioned($hospital);

        } catch (Exception $e) {
            Log::warning("Erreur lors de la récupération du statut de provisioning pour l'hôpital {$hospital->id}: " . $e->getMessage());
        }

        return $status;
    }
}
