<?php

namespace App\Core\Console\Commands;

use App\Core\Services\SchemaValidationService;
use App\Core\Services\SmartMigrationService;
use App\Core\Services\TenantConnectionService;
use App\Core\Models\Hospital;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * Commande pour synchroniser les schémas de tous les tenants
 * 
 * Vérifie et applique uniquement les changements nécessaires
 */
class TenantSchemaSyncCommand extends Command
{
    protected $signature = 'tenant:schema-sync 
                            {--hospital-id= : ID de l\'hôpital (tenant) spécifique à synchroniser}
                            {--table= : Table spécifique à synchroniser}
                            {--dry-run : Mode simulation (ne pas appliquer les changements)}
                            {--force : Forcer l\'exécution sans confirmation}';

    protected $description = 'Synchronise les schémas des tenants (vérifie et applique uniquement les changements nécessaires)';

    protected SchemaValidationService $schemaValidator;
    protected SmartMigrationService $smartMigration;

    public function __construct(
        SchemaValidationService $schemaValidator,
        SmartMigrationService $smartMigration
    ) {
        parent::__construct();
        $this->schemaValidator = $schemaValidator;
        $this->smartMigration = $smartMigration;
    }

    public function handle()
    {
        $hospitalId = $this->option('hospital-id');
        $tableFilter = $this->option('table');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        // Déterminer si on cible un tenant spécifique ou tous
        $targetSpecific = $hospitalId !== null;

        $this->info('╔══════════════════════════════════════════════════════════════╗');
        if ($targetSpecific) {
            $this->info('║  Synchronisation intelligente pour un tenant spécifique  ║');
        } else {
            $this->info('║  Synchronisation des schémas pour TOUS les tenants        ║');
        }
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        if ($dryRun) {
            $this->warn('⚠️  MODE SIMULATION - Aucun changement ne sera appliqué');
            $this->newLine();
        }

        // Récupérer les hôpitaux à traiter
        if ($targetSpecific) {
            $hospital = Hospital::find($hospitalId);
            if (!$hospital) {
                $this->error("❌ Hôpital avec l'ID {$hospitalId} introuvable.");
                return Command::FAILURE;
            }
            $hospitals = collect([$hospital]);
            
            if (!$force && !$dryRun) {
                if (!$this->confirm("⚠️  Synchroniser le schéma pour l'hôpital '{$hospital->name}' (ID: {$hospital->id}) ?", true)) {
                    $this->info('Opération annulée.');
                    return Command::SUCCESS;
                }
            }
        } else {
            if (!$force && !$dryRun) {
                if (!$this->confirm('⚠️  Cette opération va synchroniser les schémas sur TOUS les tenants. Continuer ?', false)) {
                    $this->info('Opération annulée.');
                    return Command::SUCCESS;
                }
            }
            $hospitals = Hospital::active()->get();
        }

        $this->newLine();

        $results = [];

        foreach ($hospitals as $hospital) {
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
            $this->line("   - Base de données : {$hospital->database_name}");

            $tenantConnectionService = app(TenantConnectionService::class);
            
            try {
                // Connecter au tenant
                $tenantConnectionService->connect($hospital);

                // Définir les schémas attendus pour les tables DME
                $dmeTables = $this->getDmeTableSchemas();

                foreach ($dmeTables as $tableName => $expectedSchema) {
                    // Filtrer par table si spécifié
                    if ($tableFilter && $tableName !== $tableFilter) {
                        continue;
                    }

                    $this->line("   📋 Vérification de la table: {$tableName}");

                    // Utiliser SmartMigrationService pour une migration intelligente
                    if (!$dryRun) {
                        $migrationResult = $this->smartMigration->applySmartMigration(
                            $tableName,
                            $expectedSchema,
                            function () use ($tableName, $expectedSchema) {
                                // Callback pour créer la table si elle n'existe pas
                                Schema::create($tableName, function (Blueprint $table) use ($expectedSchema) {
                                    $this->createTableStructure($table, $expectedSchema);
                                });
                            }
                        );

                        if ($migrationResult['created']) {
                            $this->info("      ✅ Table créée avec succès");
                            $results[$hospital->id][$tableName] = 'created';
                        } elseif ($migrationResult['modified']) {
                            $addedCols = implode(', ', $migrationResult['added_columns']);
                            $this->info("      ✅ Colonnes ajoutées: {$addedCols}");
                            $results[$hospital->id][$tableName] = 'modified';
                        } elseif (!empty($migrationResult['errors'])) {
                            $this->error("      ❌ Erreurs: " . implode('; ', $migrationResult['errors']));
                            $results[$hospital->id][$tableName] = 'error';
                        } else {
                            $this->info("      ✅ Schéma conforme");
                            $results[$hospital->id][$tableName] = 'ok';
                        }
                    } else {
                        // Mode dry-run : validation uniquement
                        $validation = $this->schemaValidator->validateTableSchema($tableName, $expectedSchema);

                        if (!$validation['exists']) {
                            $this->warn("      ⚠️  Table absente - serait créée");
                            $results[$hospital->id][$tableName] = 'would_create';
                        } elseif (!empty($validation['missing_columns'])) {
                            $this->warn("      ⚠️  Colonnes manquantes: " . implode(', ', $validation['missing_columns']));
                            $results[$hospital->id][$tableName] = 'would_modify';
                        } elseif (!empty($validation['different_columns'])) {
                            $this->warn("      ⚠️  Colonnes avec différences détectées");
                            $results[$hospital->id][$tableName] = 'needs_review';
                        } else {
                            $this->info("      ✅ Schéma conforme");
                            $results[$hospital->id][$tableName] = 'ok';
                        }
                    }
                }

                $this->info("   ✅ Synchronisation terminée");
            } catch (\Exception $e) {
                $this->error("   ❌ Erreur : {$e->getMessage()}");
                $results[$hospital->id]['error'] = $e->getMessage();
                
                Log::error("Erreur lors de la synchronisation du schéma pour le tenant", [
                    'hospital_id' => $hospital->id,
                    'hospital_name' => $hospital->name,
                    'error' => $e->getMessage(),
                ]);
            } finally {
                // Déconnecter du tenant pour éviter les problèmes de connexion
                try {
                    if (isset($tenantConnectionService)) {
                        $tenantConnectionService->disconnect();
                    }
                } catch (\Exception $e) {
                    // Ignorer les erreurs de déconnexion
                }
            }
        }

        $this->newLine();
        $this->displaySummary($results);

        return Command::SUCCESS;
    }

    /**
     * Définit les schémas attendus pour les tables DME
     */
    private function getDmeTableSchemas(): array
    {
        return [
            'vaccinations' => [
                'columns' => [
                    'id' => ['type' => 'bigint', 'unsigned' => true, 'autoincrement' => true],
                    'uuid' => ['type' => 'string', 'length' => 36, 'nullable' => false],
                    'patients_id' => ['type' => 'bigint', 'unsigned' => true, 'nullable' => false],
                    'movments_id' => ['type' => 'bigint', 'unsigned' => true, 'nullable' => true],
                    'vaccine_name' => ['type' => 'string', 'length' => 255, 'nullable' => false],
                    'vaccine_code' => ['type' => 'string', 'length' => 50, 'nullable' => true],
                    'vaccination_date' => ['type' => 'date', 'nullable' => false],
                    'batch_number' => ['type' => 'string', 'length' => 100, 'nullable' => true],
                    'administration_route' => ['type' => 'string', 'length' => 100, 'nullable' => true],
                    'site' => ['type' => 'string', 'length' => 100, 'nullable' => true],
                    'notes' => ['type' => 'text', 'nullable' => true],
                    'doctor_id' => ['type' => 'bigint', 'unsigned' => true, 'nullable' => true],
                    'next_dose_date' => ['type' => 'date', 'nullable' => true],
                    'created_at' => ['type' => 'timestamp', 'nullable' => true],
                    'updated_at' => ['type' => 'timestamp', 'nullable' => true],
                    'deleted_at' => ['type' => 'timestamp', 'nullable' => true],
                ],
            ],
            'prescriptions' => [
                'columns' => [
                    'id' => ['type' => 'bigint', 'unsigned' => true, 'autoincrement' => true],
                    'uuid' => ['type' => 'string', 'length' => 36, 'nullable' => false],
                    'patients_id' => ['type' => 'bigint', 'unsigned' => true, 'nullable' => false],
                    'movments_id' => ['type' => 'bigint', 'unsigned' => true, 'nullable' => true],
                    'clinical_observation_id' => ['type' => 'bigint', 'unsigned' => true, 'nullable' => true],
                    'doctor_id' => ['type' => 'bigint', 'unsigned' => true, 'nullable' => true],
                    'prescription_date' => ['type' => 'date', 'nullable' => false],
                    'notes' => ['type' => 'text', 'nullable' => true],
                    'status' => ['type' => 'enum', 'values' => ['active', 'completed', 'cancelled'], 'nullable' => false, 'default' => 'active'],
                    'valid_until' => ['type' => 'date', 'nullable' => true],
                    'created_at' => ['type' => 'timestamp', 'nullable' => true],
                    'updated_at' => ['type' => 'timestamp', 'nullable' => true],
                    'deleted_at' => ['type' => 'timestamp', 'nullable' => true],
                ],
            ],
            'prescription_items' => [
                'columns' => [
                    'id' => ['type' => 'bigint', 'unsigned' => true, 'autoincrement' => true],
                    'uuid' => ['type' => 'string', 'length' => 36, 'nullable' => false],
                    'prescription_id' => ['type' => 'bigint', 'unsigned' => true, 'nullable' => false],
                    'product_id' => ['type' => 'bigint', 'unsigned' => true, 'nullable' => true],
                    'medication_name' => ['type' => 'string', 'length' => 255, 'nullable' => false],
                    'dosage' => ['type' => 'string', 'length' => 100, 'nullable' => true],
                    'form' => ['type' => 'string', 'length' => 50, 'nullable' => true],
                    'administration_route' => ['type' => 'string', 'length' => 100, 'nullable' => true],
                    'quantity' => ['type' => 'integer', 'nullable' => true],
                    'frequency' => ['type' => 'string', 'length' => 100, 'nullable' => true],
                    'instructions' => ['type' => 'text', 'nullable' => true],
                    'duration_days' => ['type' => 'integer', 'nullable' => true],
                    'status' => ['type' => 'enum', 'values' => ['pending', 'active', 'completed', 'cancelled'], 'nullable' => false, 'default' => 'pending'],
                    'created_at' => ['type' => 'timestamp', 'nullable' => true],
                    'updated_at' => ['type' => 'timestamp', 'nullable' => true],
                    'deleted_at' => ['type' => 'timestamp', 'nullable' => true],
                ],
            ],
            'dme_documents' => [
                'columns' => [
                    'id' => ['type' => 'bigint', 'unsigned' => true, 'autoincrement' => true],
                    'uuid' => ['type' => 'string', 'length' => 36, 'nullable' => false],
                    'patients_id' => ['type' => 'bigint', 'unsigned' => true, 'nullable' => false],
                    'movments_id' => ['type' => 'bigint', 'unsigned' => true, 'nullable' => true],
                    'clinical_observation_id' => ['type' => 'bigint', 'unsigned' => true, 'nullable' => true],
                    'title' => ['type' => 'string', 'length' => 255, 'nullable' => false],
                    'type' => ['type' => 'enum', 'values' => ['certificat_medical', 'ordonnance', 'resultat_examen', 'compte_rendu', 'imagerie', 'laboratoire', 'autre'], 'nullable' => false, 'default' => 'autre'],
                    'file_path' => ['type' => 'text', 'nullable' => false],
                    'file_name' => ['type' => 'string', 'length' => 255, 'nullable' => false],
                    'mime_type' => ['type' => 'string', 'length' => 100, 'nullable' => true],
                    'file_size' => ['type' => 'integer', 'nullable' => true],
                    'description' => ['type' => 'text', 'nullable' => true],
                    'uploaded_by' => ['type' => 'bigint', 'unsigned' => true, 'nullable' => true],
                    'document_date' => ['type' => 'date', 'nullable' => true],
                    'created_at' => ['type' => 'timestamp', 'nullable' => true],
                    'updated_at' => ['type' => 'timestamp', 'nullable' => true],
                    'deleted_at' => ['type' => 'timestamp', 'nullable' => true],
                ],
            ],
        ];
    }

    /**
     * Crée la structure d'une table à partir d'un schéma
     */
    private function createTableStructure(Blueprint $table, array $schema): void
    {
        if (!isset($schema['columns'])) {
            return;
        }

        // Détecter les colonnes spéciales
        $hasIdColumn = isset($schema['columns']['id']);
        $hasTimestamps = isset($schema['columns']['created_at']) && isset($schema['columns']['updated_at']);
        $hasSoftDeletes = isset($schema['columns']['deleted_at']);
        
        // Traiter d'abord la colonne 'id' si elle existe (clé primaire)
        if ($hasIdColumn && ($schema['columns']['id']['autoincrement'] ?? false)) {
            // Utiliser bigIncrements pour la clé primaire auto-incrémentée
            $table->bigIncrements('id');
            unset($schema['columns']['id']); // Retirer de la liste pour ne pas la traiter deux fois
        }

        // Traiter les autres colonnes (sauf timestamps et soft deletes qui seront gérés séparément)
        foreach ($schema['columns'] as $columnName => $columnDef) {
            // Ignorer les colonnes qui seront gérées par les méthodes Laravel
            if ($hasTimestamps && ($columnName === 'created_at' || $columnName === 'updated_at')) {
                continue;
            }
            if ($hasSoftDeletes && $columnName === 'deleted_at') {
                continue;
            }
            $type = $columnDef['type'] ?? 'string';
            $length = $columnDef['length'] ?? null;
            $nullable = $columnDef['nullable'] ?? true;
            $default = $columnDef['default'] ?? null;
            $unsigned = $columnDef['unsigned'] ?? false;

            $column = null;

            switch ($type) {
                case 'bigint':
                    if ($unsigned) {
                        $column = $table->unsignedBigInteger($columnName);
                    } else {
                        $column = $table->bigInteger($columnName);
                    }
                    break;
                case 'integer':
                case 'int':
                    if ($unsigned) {
                        $column = $table->unsignedInteger($columnName);
                    } else {
                        $column = $table->integer($columnName);
                    }
                    break;
                case 'string':
                case 'varchar':
                    $column = $table->string($columnName, $length ?? 255);
                    break;
                case 'text':
                    $column = $table->text($columnName);
                    break;
                case 'date':
                    $column = $table->date($columnName);
                    break;
                case 'datetime':
                    $column = $table->dateTime($columnName);
                    break;
                case 'timestamp':
                    $column = $table->timestamp($columnName);
                    break;
                case 'boolean':
                    $column = $table->boolean($columnName);
                    break;
                case 'enum':
                    $values = $columnDef['values'] ?? [];
                    $column = $table->enum($columnName, $values);
                    break;
                case 'uuid':
                    $column = $table->uuid($columnName);
                    break;
                default:
                    $column = $table->string($columnName);
            }

            if ($column) {
                if (!$nullable) {
                    $column->nullable(false);
                } else {
                    $column->nullable();
                }
                
                if ($default !== null) {
                    $column->default($default);
                }
            }
        }

        // Ajouter les timestamps si created_at et updated_at existent
        if (isset($schema['columns']['created_at']) && isset($schema['columns']['updated_at'])) {
            $table->timestamps();
        }

        // Ajouter soft deletes si deleted_at existe
        if (isset($schema['columns']['deleted_at'])) {
            $table->softDeletes();
        }

        // Ajouter les clés primaires personnalisées si nécessaire
        if (isset($schema['primary_key']) && !$hasIdColumn) {
            $table->primary($schema['primary_key']);
        }

        // Ajouter les clés étrangères
        if (isset($schema['foreign_keys'])) {
            foreach ($schema['foreign_keys'] as $constraintName => $fkDef) {
                $table->foreign($fkDef['column'])
                    ->references($fkDef['references'])
                    ->on($fkDef['on'])
                    ->onUpdate($fkDef['onUpdate'] ?? 'cascade')
                    ->onDelete($fkDef['onDelete'] ?? 'restrict');
            }
        }
    }

    /**
     * Affiche le résumé des résultats
     */
    private function displaySummary(array $results): void
    {
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                    RÉSUMÉ                                   ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $total = count($results);
        $ok = 0;
        $created = 0;
        $modified = 0;
        $errors = 0;

        foreach ($results as $hospitalId => $result) {
            if (isset($result['error'])) {
                $errors++;
            } else {
                foreach ($result as $table => $status) {
                    if ($status === 'ok') $ok++;
                    elseif ($status === 'created') $created++;
                    elseif ($status === 'modified') $modified++;
                }
            }
        }

        $this->info("Total tenants: {$total}");
        $this->info("✅ Schémas conformes: {$ok}");
        $this->info("➕ Tables créées: {$created}");
        $this->info("🔧 Tables modifiées: {$modified}");
        if ($errors > 0) {
            $this->error("❌ Erreurs: {$errors}");
        }
    }
}
