<?php

namespace App\Core\Console\Commands;

use App\Core\Services\SchemaValidationService;
use App\Core\Services\TenantConnectionService;
use App\Core\Models\Hospital;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Commande pour valider l'intégrité des schémas DME de tous les tenants
 */
class TenantSchemaValidateCommand extends Command
{
    protected $signature = 'tenant:schema-validate 
                            {--table= : Table spécifique à valider}
                            {--detailed : Afficher un rapport détaillé}';

    protected $description = 'Valide l\'intégrité des schémas DME de tous les tenants';

    protected SchemaValidationService $schemaValidator;

    public function __construct(SchemaValidationService $schemaValidator)
    {
        parent::__construct();
        $this->schemaValidator = $schemaValidator;
    }

    public function handle()
    {
        $tableFilter = $this->option('table');
        $detailed = $this->option('detailed');

        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║  Validation des schémas DME pour TOUS les tenants          ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $hospitals = Hospital::active()->get();
        $results = [];
        $dmeTables = $this->getDmeTableSchemas();

        foreach ($hospitals as $hospital) {
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
            $this->line("   - Base de données : {$hospital->database_name}");

            try {
                // Connecter au tenant
                $tenantConnectionService = app(TenantConnectionService::class);
                $tenantConnectionService->connect($hospital);

                $hospitalResults = [];

                foreach ($dmeTables as $tableName => $expectedSchema) {
                    // Filtrer par table si spécifié
                    if ($tableFilter && $tableName !== $tableFilter) {
                        continue;
                    }

                    $validation = $this->schemaValidator->validateTableSchema($tableName, $expectedSchema);
                    $hospitalResults[$tableName] = $validation;

                    if ($detailed) {
                        $report = $this->schemaValidator->generateValidationReport($tableName, $expectedSchema);
                        $this->line($report);
                    } else {
                        $status = $this->getValidationStatus($validation);
                        $this->line("   📋 {$tableName}: {$status}");
                    }
                }

                $results[$hospital->id] = [
                    'name' => $hospital->name,
                    'database' => $hospital->database_name,
                    'tables' => $hospitalResults,
                ];

                $this->info("   ✅ Validation terminée");
            } catch (\Exception $e) {
                $this->error("   ❌ Erreur : {$e->getMessage()}");
                $results[$hospital->id] = [
                    'name' => $hospital->name,
                    'error' => $e->getMessage(),
                ];
            } finally {
                // Déconnecter du tenant pour éviter les problèmes de connexion
                try {
                    $tenantConnectionService->disconnect();
                } catch (\Exception $e) {
                    // Ignorer les erreurs de déconnexion
                }
            }
        }

        $this->newLine();
        $this->displaySummary($results);

        return Command::SUCCESS;
    }

    private function getValidationStatus(array $validation): string
    {
        if (!$validation['exists']) {
            return '❌ Table absente';
        }

        if (!empty($validation['missing_columns'])) {
            return '⚠️  Colonnes manquantes (' . count($validation['missing_columns']) . ')';
        }

        if (!empty($validation['different_columns'])) {
            return '⚠️  Différences détectées';
        }

        $dataInfo = $validation['has_data'] ? " ({$validation['record_count']} enregistrements)" : ' (vide)';
        return '✅ Conforme' . $dataInfo;
    }

    private function getDmeTableSchemas(): array
    {
        return [
            'vaccinations' => [
                'columns' => [
                    'id' => ['type' => 'bigint'],
                    'uuid' => ['type' => 'string'],
                    'patients_id' => ['type' => 'bigint'],
                    'movments_id' => ['type' => 'bigint', 'nullable' => true],
                    'vaccine_name' => ['type' => 'string'],
                    'vaccination_date' => ['type' => 'date'],
                ],
            ],
            'prescriptions' => [
                'columns' => [
                    'id' => ['type' => 'bigint'],
                    'uuid' => ['type' => 'string'],
                    'patients_id' => ['type' => 'bigint'],
                    'prescription_date' => ['type' => 'date'],
                    'status' => ['type' => 'enum'],
                ],
            ],
            'prescription_items' => [
                'columns' => [
                    'id' => ['type' => 'bigint'],
                    'uuid' => ['type' => 'string'],
                    'prescription_id' => ['type' => 'bigint'],
                    'medication_name' => ['type' => 'string'],
                ],
            ],
            'dme_documents' => [
                'columns' => [
                    'id' => ['type' => 'bigint'],
                    'uuid' => ['type' => 'string'],
                    'patients_id' => ['type' => 'bigint'],
                    'title' => ['type' => 'string'],
                    'file_path' => ['type' => 'text'],
                ],
            ],
        ];
    }

    private function displaySummary(array $results): void
    {
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                    RÉSUMÉ                                   ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $total = count($results);
        $valid = 0;
        $invalid = 0;
        $errors = 0;

        foreach ($results as $result) {
            if (isset($result['error'])) {
                $errors++;
            } elseif (isset($result['tables'])) {
                $allValid = true;
                foreach ($result['tables'] as $tableValidation) {
                    if (!$tableValidation['exists'] || 
                        !empty($tableValidation['missing_columns']) || 
                        !empty($tableValidation['different_columns'])) {
                        $allValid = false;
                        break;
                    }
                }
                if ($allValid) {
                    $valid++;
                } else {
                    $invalid++;
                }
            }
        }

        $this->info("Total tenants: {$total}");
        $this->info("✅ Schémas valides: {$valid}");
        if ($invalid > 0) {
            $this->warn("⚠️  Schémas à corriger: {$invalid}");
        }
        if ($errors > 0) {
            $this->error("❌ Erreurs: {$errors}");
        }
    }
}
