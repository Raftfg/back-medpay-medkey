<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;

/**
 * Commande pour exécuter une migration spécifique uniquement pour les tenants qui ne possèdent pas la table
 * 
 * Cette commande est intelligente : elle détecte automatiquement les tenants qui n'ont pas la table
 * et exécute la migration uniquement pour eux.
 */
class TenantMigrateMissingTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate-missing-table 
                            {table : Nom de la table à vérifier (ex: clinical_observations)}
                            {--migration-path= : Chemin vers la migration spécifique (optionnel)}
                            {--hospital-id= : ID de l\'hôpital spécifique (optionnel)}
                            {--dry-run : Mode simulation (ne pas appliquer les changements)}
                            {--force : Forcer l\'exécution sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exécute une migration uniquement pour les tenants qui ne possèdent pas la table spécifiée';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tableName = $this->argument('table');
        $migrationPath = $this->option('migration-path');
        $hospitalId = $this->option('hospital-id');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║  Migration intelligente pour table manquante                ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();
        $this->info("📋 Table cible : {$tableName}");
        $this->newLine();

        if ($dryRun) {
            $this->warn('⚠️  MODE SIMULATION - Aucun changement ne sera appliqué');
            $this->newLine();
        }

        // Récupérer les hôpitaux à traiter
        if ($hospitalId) {
            $hospital = Hospital::find($hospitalId);
            if (!$hospital) {
                $this->error("❌ Hôpital avec l'ID {$hospitalId} introuvable.");
                return Command::FAILURE;
            }
            $hospitals = collect([$hospital]);
        } else {
            $hospitals = Hospital::active()->get();
        }

        if ($hospitals->isEmpty()) {
            $this->warn('⚠️  Aucun tenant actif trouvé.');
            return Command::SUCCESS;
        }

        // Étape 1 : Détecter les tenants qui n'ont pas la table
        $this->info('🔍 Détection des tenants sans la table...');
        $this->newLine();

        $tenantsWithoutTable = [];
        $tenantsWithTable = [];

        $connectionService = app(TenantConnectionService::class);

        foreach ($hospitals as $hospital) {
            try {
                // Connecter au tenant
                $connectionService->connect($hospital);

                // Vérifier si la table existe
                if (!Schema::hasTable($tableName)) {
                    $tenantsWithoutTable[] = $hospital;
                    $this->line("   ⚠️  {$hospital->name} (ID: {$hospital->id}) - Table absente");
                } else {
                    $tenantsWithTable[] = $hospital;
                    $this->line("   ✅ {$hospital->name} (ID: {$hospital->id}) - Table présente");
                }
            } catch (\Exception $e) {
                $this->error("   ❌ {$hospital->name} (ID: {$hospital->id}) - Erreur : {$e->getMessage()}");
                Log::error("Erreur lors de la vérification de la table pour le tenant", [
                    'hospital_id' => $hospital->id,
                    'table' => $tableName,
                    'error' => $e->getMessage(),
                ]);
            } finally {
                try {
                    $connectionService->disconnect();
                } catch (\Exception $e) {
                    // Ignorer les erreurs de déconnexion
                }
            }
        }

        $this->newLine();
        $this->info("📊 Résultat de la détection :");
        $this->info("   ✅ Tenants avec la table : " . count($tenantsWithTable));
        $this->info("   ⚠️  Tenants sans la table : " . count($tenantsWithoutTable));
        $this->newLine();

        if (empty($tenantsWithoutTable)) {
            $this->info('✅ Tous les tenants possèdent déjà la table. Aucune action nécessaire.');
            return Command::SUCCESS;
        }

        // Confirmation
        if (!$force && !$dryRun) {
            $this->warn("⚠️  Cette opération va exécuter la migration pour " . count($tenantsWithoutTable) . " tenant(s).");
            if (!$this->confirm('Continuer ?', true)) {
                $this->info('Opération annulée.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();

        // Étape 2 : Exécuter la migration pour les tenants concernés
        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($tenantsWithoutTable as $hospital) {
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
            $this->line("   - Base de données : {$hospital->database_name}");

            try {
                // Connecter au tenant
                $connectionService->connect($hospital);

                // Purger les connexions pour éviter les conflits
                DB::purge('tenant');
                DB::purge('mysql');

                if ($dryRun) {
                    $this->warn("   [DRY-RUN] La migration serait exécutée ici");
                    $results[$hospital->id] = [
                        'hospital_id' => $hospital->id,
                        'hospital_name' => $hospital->name,
                        'status' => 'dry-run',
                    ];
                } else {
                    // Déterminer le chemin de la migration
                    if ($migrationPath) {
                        $path = $migrationPath;
                    } else {
                        // Chemin par défaut pour clinical_observations
                        if ($tableName === 'clinical_observations') {
                            // Utiliser le chemin exact du fichier de migration
                            $path = 'Modules/Movment/Database/Migrations/2026_01_24_000000_create_clinical_observations_table.php';
                        } else {
                            $this->warn("   ⚠️  Chemin de migration non spécifié. Utilisation du chemin par défaut.");
                            $path = 'database/tenant/migrations';
                        }
                    }

                    $this->line("   📦 Exécution de la migration...");
                    $this->line("      Chemin : {$path}");

                    // Vérifier si la migration a déjà été exécutée
                    $migrationName = '2026_01_24_000000_create_clinical_observations_table';
                    $migrationExists = DB::table('migrations')
                        ->where('migration', $migrationName)
                        ->exists();

                    if ($migrationExists) {
                        $this->warn("   ⚠️  La migration a déjà été exécutée, mais la table n'existe pas.");
                        $this->warn("   ℹ️  Tentative de création directe de la table...");
                        
                        // Créer la table directement en utilisant le schéma de la migration
                        $this->createClinicalObservationsTable();
                    } else {
                        // Exécuter la migration
                        Artisan::call('migrate', [
                            '--database' => 'tenant',
                            '--path' => $path,
                            '--force' => true,
                        ], $this->output);
                    }

                    // Vérifier que la table a été créée
                    if (Schema::hasTable($tableName)) {
                        $this->info("   ✅ Table créée avec succès");
                        $successCount++;
                        $results[$hospital->id] = [
                            'hospital_id' => $hospital->id,
                            'hospital_name' => $hospital->name,
                            'status' => 'success',
                        ];
                    } else {
                        $this->error("   ❌ La table n'a pas été créée");
                        $errorCount++;
                        $results[$hospital->id] = [
                            'hospital_id' => $hospital->id,
                            'hospital_name' => $hospital->name,
                            'status' => 'error',
                            'error' => 'Table non créée après migration',
                        ];
                    }
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Erreur : {$e->getMessage()}");
                $errorCount++;
                $results[$hospital->id] = [
                    'hospital_id' => $hospital->id,
                    'hospital_name' => $hospital->name,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];

                Log::error("Erreur lors de l'exécution de la migration pour le tenant", [
                    'hospital_id' => $hospital->id,
                    'table' => $tableName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            } finally {
                try {
                    $connectionService->disconnect();
                } catch (\Exception $e) {
                    // Ignorer les erreurs de déconnexion
                }
            }
        }

        // Résumé final
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                    RÉSUMÉ                                   ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();
        $this->info("Total tenants vérifiés : " . count($hospitals));
        $this->info("Tenants avec la table : " . count($tenantsWithTable));
        $this->info("Tenants traités : " . count($tenantsWithoutTable));
        
        if (!$dryRun) {
            $this->info("✅ Succès : {$successCount}");
            if ($errorCount > 0) {
                $this->error("❌ Erreurs : {$errorCount}");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Crée directement la table clinical_observations
     * Utilisé si la migration a déjà été exécutée mais que la table n'existe pas
     */
    private function createClinicalObservationsTable(): void
    {
        Schema::create('clinical_observations', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            
            // Relation patient
            $table->unsignedBigInteger('patients_id');
            $table->foreign('patients_id')
                ->references('id')
                ->on('patients')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            // Relation mouvement (optionnel - pour lier à une admission spécifique)
            $table->unsignedBigInteger('movments_id')->nullable();
            $table->foreign('movments_id')
                ->references('id')
                ->on('movments')
                ->onUpdate('cascade')
                ->onDelete('set null');
            
            // Médecin responsable (optionnel)
            $table->unsignedBigInteger('doctor_id')->nullable();
            
            // Données SOAP (Subjectif, Objectif, Analyse, Plan)
            $table->text('subjective')->nullable()->comment('Plaintes du patient');
            $table->text('objective')->nullable()->comment('Examen clinique');
            $table->text('assessment')->nullable()->comment('Diagnostic/Analyse');
            $table->text('plan')->nullable()->comment('Plan de traitement');
            
            // Signes vitaux
            $table->string('blood_pressure')->nullable()->comment('Tension artérielle');
            $table->string('heart_rate')->nullable()->comment('Fréquence cardiaque');
            $table->string('temperature')->nullable()->comment('Température');
            $table->string('respiratory_rate')->nullable()->comment('Fréquence respiratoire');
            $table->string('oxygen_saturation')->nullable()->comment('Saturation en oxygène');
            $table->string('weight')->nullable()->comment('Poids');
            $table->string('height')->nullable()->comment('Taille');
            
            // Métadonnées
            $table->dateTime('observation_date')->nullable()->comment('Date de l\'observation');
            $table->string('type')->default('consultation')->comment('Type: consultation, urgence, suivi, etc.');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
