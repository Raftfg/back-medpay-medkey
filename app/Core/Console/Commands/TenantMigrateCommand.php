<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * Commande pour exécuter les migrations d'un tenant spécifique
 * 
 * @package App\Core\Console\Commands
 */
class TenantMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate 
                            {hospital_id : ID de l\'hôpital (tenant)}
                            {--fresh : Supprimer toutes les tables avant de migrer}
                            {--seed : Exécuter les seeders après la migration}
                            {--path= : Chemin vers les migrations à exécuter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exécute les migrations pour un tenant (hôpital) spécifique';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $hospitalId = $this->argument('hospital_id');
        $fresh = $this->option('fresh');
        $seed = $this->option('seed');
        $migrationPath = $this->option('path') ?? database_path('tenant/migrations');

        // Récupérer l'hôpital
        $hospital = Hospital::find($hospitalId);
        
        if (!$hospital) {
            $this->error("❌ Hôpital avec l'ID {$hospitalId} introuvable.");
            return Command::FAILURE;
        }

        $this->info("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
        $this->line("   - Domaine : {$hospital->domain}");
        $this->line("   - Base de données : {$hospital->database_name}");

        // Vérifier que la base de données existe
        if (!$this->databaseExists($hospital)) {
            $this->error("❌ La base de données '{$hospital->database_name}' n'existe pas.");
            $this->warn("💡 Créez-la d'abord avec :");
            $this->line("   CREATE DATABASE `{$hospital->database_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            return Command::FAILURE;
        }

        // Connecter au tenant
        try {
            $connectionService = app(TenantConnectionService::class);
            $connectionService->connect($hospital);
            
            $this->info("✅ Connecté à la base de données tenant");
        } catch (\Exception $e) {
            $this->error("❌ Erreur de connexion : {$e->getMessage()}");
            return Command::FAILURE;
        }

        // Exécuter les migrations
        try {
            $this->newLine();
            $this->info("📦 Exécution des migrations...");

            if ($fresh) {
                $this->warn("⚠️  Mode FRESH : toutes les tables seront supprimées !");
                if (!$this->confirm('Êtes-vous sûr ?', false)) {
                    $this->info('Opération annulée.');
                    return Command::SUCCESS;
                }
                
                Artisan::call('migrate:fresh', [
                    '--database' => 'tenant',
                    '--path' => $migrationPath,
                    '--force' => true,
                ]);
            } else {
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => $migrationPath,
                    '--force' => true,
                ]);
            }

            $this->info("✅ Migrations exécutées avec succès !");

            // Exécuter les seeders si demandé
            if ($seed) {
                $this->newLine();
                $this->info("🌱 Exécution des seeders...");
                
                Artisan::call('db:seed', [
                    '--database' => 'tenant',
                    '--force' => true,
                ]);
                
                $this->info("✅ Seeders exécutés avec succès !");
            }

            // Mettre à jour le statut de l'hôpital si c'était en provisioning
            if ($hospital->isProvisioning()) {
                $hospital->update([
                    'status' => 'active',
                    'provisioned_at' => now(),
                ]);
                $this->info("✅ Statut de l'hôpital mis à jour : active");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de l'exécution des migrations :");
            $this->error($e->getMessage());
            
            if ($this->getOutput()->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        } finally {
            // Déconnecter
            app(TenantConnectionService::class)->disconnect();
        }
    }

    /**
     * Vérifie si la base de données existe
     *
     * @param  Hospital  $hospital
     * @return bool
     */
    protected function databaseExists(Hospital $hospital): bool
    {
        try {
            $config = $hospital->getDatabaseConfig();
            $connectionService = app(TenantConnectionService::class);
            
            return $connectionService->testConnection($hospital);
        } catch (\Exception $e) {
            return false;
        }
    }
}
