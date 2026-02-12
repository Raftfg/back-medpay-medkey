<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Commande Artisan : Suppression des colonnes hospital_id des tables tenant
 * 
 * Cette commande supprime les colonnes hospital_id et leurs contraintes
 * des bases de données tenant, car l'isolation est maintenant assurée
 * par la séparation des bases de données.
 * 
 * @package App\Core\Console\Commands
 */
class RemoveHospitalIdCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:remove-hospital-id 
                            {hospital_id : ID de l\'hôpital (tenant)}
                            {--dry-run : Mode simulation (ne fait rien)}
                            {--force : Forcer la suppression même si des données existent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprime les colonnes hospital_id des tables tenant';

    /**
     * Service de connexion tenant
     *
     * @var TenantConnectionService
     */
    protected TenantConnectionService $tenantConnectionService;

    /**
     * Constructeur
     *
     * @param TenantConnectionService $tenantConnectionService
     */
    public function __construct(TenantConnectionService $tenantConnectionService)
    {
        parent::__construct();
        $this->tenantConnectionService = $tenantConnectionService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("╔══════════════════════════════════════════════════════════════╗");
        $this->info("║  Suppression des colonnes hospital_id des tables tenant    ║");
        $this->info("╚══════════════════════════════════════════════════════════════╝\n");

        $hospitalId = $this->argument('hospital_id');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        // Récupérer l'hôpital
        $hospital = Hospital::find($hospitalId);
        
        if (!$hospital) {
            $this->error("❌ Hôpital avec l'ID {$hospitalId} introuvable.");
            return Command::FAILURE;
        }

        $this->info("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
        $this->line("   - Domaine : {$hospital->domain}");
        $this->line("   - Base de données : {$hospital->database_name}\n");

        if ($dryRun) {
            $this->warn("⚠️  MODE SIMULATION - Aucune modification ne sera effectuée\n");
        }

        // Connecter à la base tenant
        try {
            $this->tenantConnectionService->connect($hospital);
            $this->info("✅ Connecté à la base de données tenant\n");
        } catch (\Exception $e) {
            $this->error("❌ Erreur de connexion : {$e->getMessage()}");
            return Command::FAILURE;
        }

        // Détecter les tables avec hospital_id
        $tablesWithHospitalId = $this->detectTablesWithHospitalId();

        if ($tablesWithHospitalId->isEmpty()) {
            $this->info("✅ Aucune table avec hospital_id trouvée. Rien à supprimer.");
            return Command::SUCCESS;
        }

        $this->info("📋 {$tablesWithHospitalId->count()} table(s) avec hospital_id détectée(s)\n");

        // Demander confirmation
        if (!$dryRun && !$force) {
            if (!$this->confirm("⚠️  Cette opération est IRREVERSIBLE. Continuer ?", false)) {
                $this->info("Opération annulée.");
                return Command::SUCCESS;
            }
        }

        // Supprimer hospital_id de chaque table
        $successCount = 0;
        $errorCount = 0;
        $progressBar = $this->output->createProgressBar($tablesWithHospitalId->count());
        $progressBar->start();

        foreach ($tablesWithHospitalId as $table) {
            try {
                if ($dryRun) {
                    $this->newLine();
                    $this->line("    [DRY-RUN] Table {$table} : hospital_id serait supprimé");
                } else {
                    $this->removeHospitalIdFromTable($table);
                    $this->newLine();
                    $this->info("    ✅ Table {$table} : hospital_id supprimé");
                }
                $successCount++;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("    ❌ Erreur pour la table {$table} : {$e->getMessage()}");
                $errorCount++;
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Résumé
        $this->info("╔══════════════════════════════════════════════════════════════╗");
        $this->info("║                        RÉSUMÉ                                ║");
        $this->info("╚══════════════════════════════════════════════════════════════╝");
        $this->info("✅ Succès: {$successCount}");
        $this->info("❌ Erreurs: {$errorCount}\n");

        if ($errorCount > 0) {
            $this->error("❌ Suppression terminée avec des erreurs.");
            return Command::FAILURE;
        } else {
            $this->info("✅ Suppression terminée avec succès !");
            return Command::SUCCESS;
        }
    }

    /**
     * Détecte les tables avec hospital_id dans la base tenant
     *
     * @return \Illuminate\Support\Collection
     */
    protected function detectTablesWithHospitalId()
    {
        $tenantConnection = $this->tenantConnectionService->getCurrentConnection();
        $database = $tenantConnection->getDatabaseName();

        // Récupérer toutes les tables
        $tables = $tenantConnection->select("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? ORDER BY TABLE_NAME", [$database]);
        
        $tablesWithHospitalId = collect();

        foreach ($tables as $table) {
            $tableName = $table->TABLE_NAME;
            
            // Vérifier si la table a hospital_id
            $hasHospitalId = $tenantConnection->select("
                SELECT COUNT(*) as count 
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = 'hospital_id'
            ", [$database, $tableName]);
            
            if (!empty($hasHospitalId) && isset($hasHospitalId[0]->count) && $hasHospitalId[0]->count > 0) {
                $tablesWithHospitalId->push($tableName);
            }
        }

        return $tablesWithHospitalId;
    }

    /**
     * Supprime hospital_id d'une table spécifique
     *
     * @param  string  $table
     * @return void
     * @throws \Exception
     */
    protected function removeHospitalIdFromTable(string $table): void
    {
        $tenantConnection = $this->tenantConnectionService->getCurrentConnection();
        $database = $tenantConnection->getDatabaseName();

        // 1. Supprimer les contraintes de clés étrangères vers hospitals
        $foreignKeys = $tenantConnection->select("
            SELECT CONSTRAINT_NAME 
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = 'hospital_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$database, $table]);

        foreach ($foreignKeys as $fk) {
            $constraintName = $fk->CONSTRAINT_NAME;
            try {
                $tenantConnection->statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraintName}`");
            } catch (\Exception $e) {
                // Ignorer si la contrainte n'existe pas
                if (strpos($e->getMessage(), 'Unknown key') === false) {
                    throw $e;
                }
            }
        }

        // 2. Supprimer les index sur hospital_id
        $indexes = $tenantConnection->select("
            SELECT INDEX_NAME 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = ? 
            AND TABLE_NAME = ? 
            AND COLUMN_NAME = 'hospital_id'
            AND INDEX_NAME != 'PRIMARY'
        ", [$database, $table]);

        foreach ($indexes as $index) {
            $indexName = $index->INDEX_NAME;
            try {
                $tenantConnection->statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
            } catch (\Exception $e) {
                // Ignorer si l'index n'existe pas
                if (strpos($e->getMessage(), 'Unknown key') === false) {
                    throw $e;
                }
            }
        }

        // 3. Supprimer la colonne hospital_id
        $tenantConnection->statement("ALTER TABLE `{$table}` DROP COLUMN `hospital_id`");
    }
}
