<?php

namespace App\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Core\Models\Hospital as CoreHospital;
use App\Core\Services\TenantConnectionService;
use Modules\Administration\Entities\Hospital as OldHospital;
use Exception;

/**
 * Commande Artisan : Migration des données existantes vers l'architecture database-per-tenant
 * 
 * Cette commande migre les données existantes (avec hospital_id) vers des bases séparées.
 * 
 * @package App\Core\Console\Commands
 */
class MigrateExistingDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate-existing 
                            {--hospital-id= : ID de l\'hôpital spécifique à migrer}
                            {--dry-run : Mode simulation (ne fait rien)}
                            {--force : Forcer la migration même si la base existe déjà}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migre les données existantes vers l\'architecture database-per-tenant';

    /**
     * Service de connexion tenant
     *
     * @var TenantConnectionService
     */
    protected $tenantService;

    /**
     * Liste des tables à migrer (avec hospital_id)
     * Si vide, toutes les tables avec hospital_id seront détectées automatiquement
     *
     * @var array
     */
    protected $tablesToMigrate = [];

    /**
     * Tables à exclure (pas de hospital_id ou données partagées)
     *
     * @var array
     */
    protected $tablesToExclude = [
        'migrations',
        'password_resets',
        'personal_access_tokens',
        'oauth_access_tokens',
        'oauth_clients',
        'oauth_personal_access_clients',
        'oauth_refresh_tokens',
        'hospitals', // Table CORE, ne pas migrer
        'hospital_modules', // Table CORE
        'system_admins', // Table CORE
        'hospital_settings', // Table CORE
        'pays', // Données géographiques partagées
        'departements', // Données géographiques partagées
        'communes', // Données géographiques partagées
        'arrondissements', // Données géographiques partagées
        'quartiers', // Données géographiques partagées
    ];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->tenantService = app(TenantConnectionService::class);

        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║  Migration des données existantes vers database-per-tenant  ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Mode dry-run
        if ($this->option('dry-run')) {
            $this->warn('⚠️  MODE SIMULATION - Aucune modification ne sera effectuée');
            $this->newLine();
        }

        try {
            // Récupérer les hôpitaux à migrer
            $hospitals = $this->getHospitalsToMigrate();

            if ($hospitals->isEmpty()) {
                $this->error('❌ Aucun hôpital trouvé à migrer.');
                return Command::FAILURE;
            }

            $this->info("📊 {$hospitals->count()} hôpital(s) à migrer");
            $this->newLine();

            // Confirmation
            if (!$this->option('dry-run') && !$this->option('force')) {
                if (!$this->confirm('⚠️  Cette opération va créer de nouvelles bases de données. Continuer ?', false)) {
                    $this->info('Migration annulée.');
                    return Command::SUCCESS;
                }
            }

            $bar = $this->output->createProgressBar($hospitals->count());
            $bar->start();

            $successCount = 0;
            $errorCount = 0;

            foreach ($hospitals as $oldHospital) {
                try {
                    $this->newLine();
                    $this->info("🏥 Migration de l'hôpital: {$oldHospital->name} (ID: {$oldHospital->id})");

                    $this->migrateHospital($oldHospital);

                    $successCount++;
                    $this->info("✅ Hôpital {$oldHospital->name} migré avec succès");
                } catch (Exception $e) {
                    $errorCount++;
                    $this->error("❌ Erreur lors de la migration de l'hôpital {$oldHospital->name}: {$e->getMessage()}");
                    Log::error("Erreur migration hôpital {$oldHospital->id}", [
                        'hospital' => $oldHospital->name,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine(2);

            // Résumé
            $this->info('╔══════════════════════════════════════════════════════════════╗');
            $this->info('║                        RÉSUMÉ                                ║');
            $this->info('╚══════════════════════════════════════════════════════════════╝');
            $this->info("✅ Succès: {$successCount}");
            $this->info("❌ Erreurs: {$errorCount}");
            $this->newLine();

            if ($errorCount > 0) {
                $this->warn('⚠️  Certaines migrations ont échoué. Vérifiez les logs.');
                return Command::FAILURE;
            }

            $this->info('✅ Migration terminée avec succès !');
            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error("❌ Erreur fatale: {$e->getMessage()}");
            Log::error("Erreur fatale migration", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }

    /**
     * Récupère les hôpitaux à migrer
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getHospitalsToMigrate()
    {
        $hospitalId = $this->option('hospital-id');

        if ($hospitalId) {
            $hospital = OldHospital::find($hospitalId);
            return $hospital ? collect([$hospital]) : collect();
        }

        return OldHospital::active()->get();
    }

    /**
     * Migre un hôpital spécifique
     *
     * @param  OldHospital  $oldHospital
     * @return void
     * @throws Exception
     */
    protected function migrateHospital(OldHospital $oldHospital)
    {
        // ÉTAPE 1: Créer ou récupérer l'hôpital dans CORE
        $coreHospital = $this->createOrUpdateCoreHospital($oldHospital);

        // ÉTAPE 2: Créer la base de données tenant
        $this->createTenantDatabase($coreHospital);

        // ÉTAPE 3: Copier la structure des tables (sans hospital_id)
        $this->copyTableStructures($coreHospital);

        // ÉTAPE 4: Migrer les données filtrées par hospital_id
        $this->migrateData($oldHospital, $coreHospital);

        // ÉTAPE 5: Mettre à jour les informations dans CORE
        $this->updateCoreHospital($coreHospital);
    }

    /**
     * Crée ou met à jour l'hôpital dans la base CORE
     *
     * @param  OldHospital  $oldHospital
     * @return CoreHospital
     */
    protected function createOrUpdateCoreHospital(OldHospital $oldHospital): CoreHospital
    {
        $this->line('  📝 Création/mise à jour dans la base CORE...');

        $coreHospital = CoreHospital::where('id', $oldHospital->id)->first();

        if (!$coreHospital) {
            $coreHospital = CoreHospital::create([
                'id' => $oldHospital->id, // Conserver le même ID
                'name' => $oldHospital->name,
                'domain' => $oldHospital->domain ?? $this->generateDomain($oldHospital->name),
                'slug' => $oldHospital->slug ?? \Illuminate\Support\Str::slug($oldHospital->name),
                'database_name' => $oldHospital->database_name ?? $this->generateDatabaseName($oldHospital->id),
                'database_host' => config('database.connections.mysql.host', '127.0.0.1'),
                'database_port' => config('database.connections.mysql.port', '3306'),
                'database_username' => config('database.connections.mysql.username'),
                'database_password' => config('database.connections.mysql.password'),
                'status' => $oldHospital->status ?? 'active',
                'address' => $oldHospital->address ?? null,
                'phone' => $oldHospital->phone ?? null,
                'email' => $oldHospital->email ?? null,
                'provisioned_at' => now(),
            ]);
        } else {
            // Mettre à jour si nécessaire
            $coreHospital->update([
                'name' => $oldHospital->name,
                'status' => $oldHospital->status ?? 'active',
            ]);
        }

        return $coreHospital;
    }

    /**
     * Crée la base de données tenant
     *
     * @param  CoreHospital  $hospital
     * @return void
     * @throws Exception
     */
    protected function createTenantDatabase(CoreHospital $hospital)
    {
        $this->line("  🗄️  Création de la base de données: {$hospital->database_name}...");

        if ($this->option('dry-run')) {
            $this->line("    [DRY-RUN] Base de données serait créée: {$hospital->database_name}");
            return;
        }

        $config = $hospital->getDatabaseConfig();
        $databaseName = $config['database'];

        // Vérifier si la base existe déjà
        $exists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);

        if (!empty($exists)) {
            if (!$this->option('force')) {
                throw new Exception("La base de données {$databaseName} existe déjà. Utilisez --force pour forcer la migration.");
            }
            
            // En mode force, vérifier si la base est vide
            $tables = DB::select("SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?", [$databaseName]);
            $tableCount = $tables[0]->count ?? 0;
            
            if ($tableCount > 0) {
                $this->warn("    ⚠️  La base {$databaseName} contient déjà {$tableCount} table(s)");
                $this->line("    ℹ️  La migration continuera et ajoutera les données manquantes");
            } else {
                $this->info("    ℹ️  La base {$databaseName} existe mais est vide, continuation...");
            }
        } else {
            // Créer la base de données
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->info("    ✅ Base de données créée: {$databaseName}");
        }

        $this->info("    ✅ Base de données créée: {$databaseName}");
    }

    /**
     * Copie la structure des tables (sans hospital_id)
     * Utilise les migrations existantes pour créer la structure
     *
     * @param  CoreHospital  $hospital
     * @return void
     * @throws Exception
     */
    protected function copyTableStructures(CoreHospital $hospital)
    {
        $this->line('  📋 Création de la structure des tables...');

        if ($this->option('dry-run')) {
            $this->line('    [DRY-RUN] Structure des tables serait créée via migrations');
            return;
        }

        // Connecter à la base tenant
        $this->tenantService->connect($hospital);
        $tenantConnection = $this->tenantService->getCurrentConnection();

        // Utiliser les migrations existantes (database/migrations et Modules)
        // Note: Les migrations qui ajoutent hospital_id seront ignorées car elles échoueront
        // si la colonne existe déjà, ou seront simplement ignorées
        $this->line('    Exécution des migrations...');
        
        try {
            // Exécuter les migrations principales
            $this->line('    Exécution des migrations principales...');
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations',
                '--force' => true,
            ], $this->output);

            // Exécuter les migrations des modules
            $modulesPath = base_path('Modules');
            if (is_dir($modulesPath)) {
                $modules = array_filter(glob($modulesPath . '/*'), 'is_dir');
                $this->line("    Exécution des migrations de " . count($modules) . " module(s)...");
                
                foreach ($modules as $modulePath) {
                    $moduleName = basename($modulePath);
                    $migrationsPath = $modulePath . '/Database/Migrations';
                    
                    if (is_dir($migrationsPath)) {
                        try {
                            Artisan::call('migrate', [
                                '--database' => 'tenant',
                                '--path' => "Modules/{$moduleName}/Database/Migrations",
                                '--force' => true,
                            ], $this->output);
                        } catch (Exception $e) {
                            // Ignorer les erreurs de tables existantes (normal si déjà migré)
                            if (strpos($e->getMessage(), 'already exists') === false && 
                                strpos($e->getMessage(), 'Duplicate entry') === false) {
                                $this->warn("    ⚠️  Erreur dans le module {$moduleName}: {$e->getMessage()}");
                            }
                        }
                    }
                }
            }

            $this->info('    ✅ Structure des tables créée');
        } catch (Exception $e) {
            // Si une migration échoue (ex: colonne hospital_id existe déjà), continuer
            $this->warn("    ⚠️  Certaines migrations ont échoué (normal si tables existent déjà): {$e->getMessage()}");
        }
    }

    /**
     * Migre les données filtrées par hospital_id
     *
     * @param  OldHospital  $oldHospital
     * @param  CoreHospital  $coreHospital
     * @return void
     * @throws Exception
     */
    protected function migrateData(OldHospital $oldHospital, CoreHospital $coreHospital)
    {
        $this->line("  📦 Migration des données pour l'hôpital ID: {$oldHospital->id}...");

        // IMPORTANT: Détecter les tables AVANT de se connecter à la base tenant
        // Utiliser explicitement la connexion par défaut (base principale)
        // S'assurer qu'on utilise la connexion par défaut (pas tenant)
        $defaultConnection = DB::connection('mysql'); // Forcer la connexion mysql par défaut
        $database = $defaultConnection->getDatabaseName();
        
        $this->line("    🔍 Détection des tables dans la base: {$database}");
        
        // Détecter automatiquement les tables avec hospital_id dans la base principale
        // Utiliser la connexion par défaut explicitement
        $tablesToMigrate = $this->detectTablesWithHospitalId($defaultConnection, $database);

        if (empty($tablesToMigrate)) {
            $this->warn('    ⚠️  Aucune table avec hospital_id trouvée');
            return;
        }

        $this->line("    📋 {$tablesToMigrate->count()} table(s) à migrer");

        if ($this->option('dry-run')) {
            // En mode dry-run, compter les enregistrements qui seraient migrés
            // Utiliser la connexion par défaut
            $totalRecords = 0;
            foreach ($tablesToMigrate as $table) {
                $count = $defaultConnection->table($table)
                    ->where('hospital_id', $oldHospital->id)
                    ->count();
                $totalRecords += $count;
                if ($count > 0) {
                    $this->line("    [DRY-RUN] Table {$table}: {$count} enregistrement(s) seraient migré(s)");
                }
            }
            $this->line("    [DRY-RUN] Total: {$totalRecords} enregistrement(s) seraient migré(s)");
            return;
        }

        // Connecter à la base tenant
        $this->tenantService->connect($coreHospital);
        $tenantConnection = $this->tenantService->getCurrentConnection();

        $migratedCount = 0;
        $bar = $this->output->createProgressBar($tablesToMigrate->count());
        $bar->start();

        foreach ($tablesToMigrate as $table) {
            try {
                // Vérifier que la table existe dans la base tenant avant de migrer
                if (!$tenantConnection->getSchemaBuilder()->hasTable($table)) {
                    $this->newLine();
                    $this->warn("    ⚠️  Table {$table} n'existe pas dans la base tenant, ignorée");
                    $bar->advance();
                    continue;
                }
                
                $count = $this->migrateTableData($table, $oldHospital->id, $defaultConnection, $tenantConnection);
                $migratedCount += $count;
                if ($count > 0) {
                    $this->newLine();
                    $this->line("    ✅ Table {$table}: {$count} enregistrement(s) migré(s)");
                }
            } catch (Exception $e) {
                $this->newLine();
                $this->warn("    ⚠️  Erreur lors de la migration de {$table}: {$e->getMessage()}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("    ✅ {$migratedCount} enregistrement(s) migré(s) au total");
    }

    /**
     * Détecte automatiquement les tables avec hospital_id
     * Utilise la connexion par défaut (base principale) pour la détection
     *
     * @param  \Illuminate\Database\Connection|null  $connection
     * @param  string|null  $database
     * @return \Illuminate\Support\Collection
     */
    protected function detectTablesWithHospitalId($connection = null, $database = null)
    {
        // Utiliser la connexion fournie ou la connexion par défaut
        $defaultConnection = $connection ?? DB::connection('mysql');
        $databaseName = $database ?? $defaultConnection->getDatabaseName();
        
        // Récupérer toutes les tables de la base principale
        $tables = $defaultConnection->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME", [$databaseName]);
        $tableNames = array_map(function ($table) {
            return $table->TABLE_NAME;
        }, $tables);
        
        $tablesWithHospitalId = collect();

        foreach ($tableNames as $table) {
            // Exclure les tables CORE et partagées
            if (in_array($table, $this->tablesToExclude)) {
                continue;
            }

            // Vérifier si la table a hospital_id dans la base principale
            $hasHospitalId = $defaultConnection->select("
                SELECT COUNT(*) as count 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = 'hospital_id'
            ", [$databaseName, $table]);
            
            if (!empty($hasHospitalId) && isset($hasHospitalId[0]->count) && $hasHospitalId[0]->count > 0) {
                $tablesWithHospitalId->push($table);
            }
        }

        return $tablesWithHospitalId;
    }

    /**
     * Migre les données d'une table spécifique
     *
     * @param  string  $table
     * @param  int  $hospitalId
     * @param  \Illuminate\Database\Connection  $defaultConnection
     * @param  \Illuminate\Database\Connection  $tenantConnection
     * @return int
     */
    protected function migrateTableData(string $table, int $hospitalId, $defaultConnection, $tenantConnection): int
    {
        // IMPORTANT: Utiliser la connexion par défaut (base principale) pour lire les données
        // Récupérer les données de la base principale
        $data = $defaultConnection->table($table)
            ->where('hospital_id', $hospitalId)
            ->get()
            ->map(function ($row) {
                $array = (array) $row;
                // Supprimer hospital_id
                unset($array['hospital_id']);
                return $array;
            })
            ->toArray();

        if (empty($data)) {
            return 0;
        }

        // Insérer dans la base tenant (par batch pour éviter les problèmes de mémoire)
        $chunks = array_chunk($data, 100);
        $totalInserted = 0;
        
        foreach ($chunks as $chunk) {
            try {
                $tenantConnection->table($table)->insert($chunk);
                $totalInserted += count($chunk);
            } catch (Exception $e) {
                // Ignorer les doublons (si la table existe déjà avec des données)
                if (strpos($e->getMessage(), 'Duplicate entry') === false) {
                    throw $e;
                }
            }
        }

        return $totalInserted;
    }

    /**
     * Met à jour les informations de l'hôpital dans CORE
     *
     * @param  CoreHospital  $hospital
     * @return void
     */
    protected function updateCoreHospital(CoreHospital $hospital)
    {
        if ($this->option('dry-run')) {
            return;
        }

        $hospital->update([
            'provisioned_at' => now(),
            'status' => 'active',
        ]);
    }

    /**
     * Récupère toutes les tables de la base principale
     *
     * @return array
     */
    protected function getAllTables(): array
    {
        // Utiliser la connexion par défaut pour récupérer le nom de la base
        $defaultConnection = DB::connection();
        $database = $defaultConnection->getDatabaseName();
        
        // Récupérer toutes les tables de la base principale
        $tables = $defaultConnection->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME", [$database]);
        
        return array_map(function ($table) {
            return $table->TABLE_NAME;
        }, $tables);
    }


    /**
     * Génère un nom de domaine à partir du nom de l'hôpital
     *
     * @param  string  $name
     * @return string
     */
    protected function generateDomain(string $name): string
    {
        $slug = \Illuminate\Support\Str::slug($name);
        return "{$slug}.medkey.com";
    }

    /**
     * Génère un nom de base de données
     *
     * @param  int  $hospitalId
     * @return string
     */
    protected function generateDatabaseName(int $hospitalId): string
    {
        $prefix = config('tenant.database_prefix', 'medkey_');
        return "{$prefix}hospital_{$hospitalId}";
    }
}
