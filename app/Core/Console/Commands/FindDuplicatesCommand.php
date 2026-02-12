<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Commande pour détecter et nettoyer les doublons dans les tables
 */
class FindDuplicatesCommand extends Command
{
    protected $signature = 'tenant:find-duplicates 
                            {--hospital-id= : ID de l\'hôpital spécifique (optionnel)}
                            {--table= : Table spécifique à vérifier (optionnel)}
                            {--clean : Nettoyer automatiquement les doublons (garde le plus récent)}
                            {--dry-run : Mode simulation (ne pas supprimer)}
                            {--details : Afficher les détails des doublons}
                            {--all-tables : Vérifier toutes les tables (pas seulement les tables DME)}
                            {--force : Forcer l\'exécution sans confirmation}';

    protected $description = 'Détecte et nettoie les doublons dans les tables des tenants';

    public function handle()
    {
        $hospitalId = $this->option('hospital-id');
        $tableFilter = $this->option('table');
        $clean = $this->option('clean');
        $dryRun = $this->option('dry-run');

        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║  Détection et nettoyage des doublons                      ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $force = $this->option('force');
        
        if ($clean && !$dryRun && !$force) {
            if (!$this->confirm('⚠️  Cette opération va supprimer les doublons. Continuer ?', false)) {
                $this->info('Opération annulée.');
                return Command::SUCCESS;
            }
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

        $connectionService = app(TenantConnectionService::class);
        $totalDuplicates = 0;
        $totalCleaned = 0;

        foreach ($hospitals as $hospital) {
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
            $this->line("   - Base de données : {$hospital->database_name}");
            $this->newLine();

            try {
                $connectionService->connect($hospital);

                // Tables à vérifier (avec leurs colonnes de détection de doublons)
                if ($this->option('all-tables')) {
                    $tablesToCheck = $this->getAllTablesToCheck($tableFilter);
                } else {
                    $tablesToCheck = $this->getTablesToCheck($tableFilter);
                }

                foreach ($tablesToCheck as $tableName => $config) {
                    if (!Schema::hasTable($tableName)) {
                        continue;
                    }

                    $this->line("   📋 Vérification de la table: {$tableName}");

                    // Vérifier les doublons par UUID
                    $duplicates = $this->findDuplicates($tableName, $config);
                    
                    // Vérifier les doublons supplémentaires si configurés
                    if (isset($config['additional_checks'])) {
                        foreach ($config['additional_checks'] as $checkColumns) {
                            $additionalConfig = ['columns' => $checkColumns, 'keep' => $config['keep']];
                            $additionalDups = $this->findDuplicates($tableName, $additionalConfig);
                            
                            if ($additionalDups['count'] > 0) {
                                $duplicates['count'] += $additionalDups['count'];
                                $duplicates['groups'] = array_merge($duplicates['groups'], $additionalDups['groups']);
                            }
                        }
                    }
                    
                    if ($duplicates['count'] > 0) {
                        $this->warn("      ⚠️  {$duplicates['count']} doublon(s) détecté(s)");
                        
                        // Afficher les détails des doublons
                        if ($this->option('details') || $duplicates['count'] <= 10) {
                            foreach ($duplicates['groups'] as $group) {
                                $valuesStr = implode(', ', array_map(function($k, $v) {
                                    $val = is_null($v) ? 'NULL' : (string)$v;
                                    return "{$k}=" . (strlen($val) > 30 ? substr($val, 0, 30) . '...' : $val);
                                }, array_keys($group['values']), $group['values']));
                                $this->line("         - {$valuesStr} : {$group['count']} doublon(s)");
                            }
                        }
                        
                        $totalDuplicates += $duplicates['count'];

                        if ($clean && !$dryRun) {
                            $cleaned = $this->cleanDuplicates($tableName, $config, $duplicates);
                            $this->info("      ✅ {$cleaned} doublon(s) supprimé(s)");
                            $totalCleaned += $cleaned;
                        } elseif ($dryRun) {
                            $this->line("      [DRY-RUN] {$duplicates['count']} doublon(s) seraient supprimés");
                        }
                    } else {
                        $this->info("      ✅ Aucun doublon");
                    }
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Erreur : {$e->getMessage()}");
            } finally {
                try {
                    $connectionService->disconnect();
                } catch (\Exception $e) {
                    // Ignorer
                }
            }
        }

        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                    RÉSUMÉ                                   ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();
        $this->info("Total doublons détectés : {$totalDuplicates}");
        if ($clean && !$dryRun) {
            $this->info("Total doublons supprimés : {$totalCleaned}");
        }

        return Command::SUCCESS;
    }

    /**
     * Définit les tables à vérifier et leurs critères de doublons
     */
    private function getTablesToCheck(?string $tableFilter): array
    {
        $tables = [
            'clinical_observations' => [
                'columns' => ['uuid'],
                'additional_checks' => [
                    ['patients_id', 'observation_date'] // Même patient + même date
                ],
                'keep' => 'newest' // Garder le plus récent
            ],
            'vaccinations' => [
                'columns' => ['uuid'],
                'additional_checks' => [
                    ['patients_id', 'vaccination_date', 'vaccine_name'] // Même patient + même date + même vaccin
                ],
                'keep' => 'newest'
            ],
            'prescriptions' => [
                'columns' => ['uuid'],
                'additional_checks' => [
                    // Pas de vérification supplémentaire car un patient peut avoir plusieurs prescriptions le même jour
                ],
                'keep' => 'newest'
            ],
            'prescription_items' => [
                'columns' => ['uuid'],
                'keep' => 'newest'
            ],
            'dme_documents' => [
                'columns' => ['uuid'],
                'additional_checks' => [
                    ['patients_id', 'file_path'] // Même patient + même chemin de fichier (vrai doublon)
                ],
                'keep' => 'newest'
            ],
            'antecedents' => [
                'columns' => ['uuid'],
                'additional_checks' => [
                    ['patients_id', 'name', 'type'] // Même patient + même nom + même type
                ],
                'keep' => 'newest'
            ],
            'allergies' => [
                'columns' => ['uuid'],
                'additional_checks' => [
                    ['patients_id', 'name'] // Même patient + même nom
                ],
                'keep' => 'newest'
            ],
        ];

        if ($tableFilter) {
            return array_filter($tables, function($key) use ($tableFilter) {
                return $key === $tableFilter;
            }, ARRAY_FILTER_USE_KEY);
        }

        return $tables;
    }

    /**
     * Récupère toutes les tables de la base de données pour vérification
     */
    private function getAllTablesToCheck(?string $tableFilter): array
    {
        $allTables = [];
        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . DB::connection()->getDatabaseName();

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            
            // Ignorer les tables système
            if (in_array($tableName, ['migrations', 'password_resets', 'failed_jobs', 'personal_access_tokens'])) {
                continue;
            }

            if ($tableFilter && $tableName !== $tableFilter) {
                continue;
            }

            // Détecter automatiquement les colonnes uniques
            $config = $this->detectUniqueColumns($tableName);
            if ($config) {
                $allTables[$tableName] = $config;
            }
        }

        return $allTables;
    }

    /**
     * Détecte automatiquement les colonnes uniques dans une table
     */
    private function detectUniqueColumns(string $tableName): ?array
    {
        $config = [
            'columns' => [],
            'additional_checks' => [],
            'keep' => 'newest'
        ];

        // Vérifier si la table a une colonne UUID (critère principal)
        if (Schema::hasColumn($tableName, 'uuid')) {
            $config['columns'][] = 'uuid';
        }

        // Si pas d'UUID, chercher d'autres colonnes uniques courantes
        if (empty($config['columns'])) {
            // Vérifier les colonnes communes qui devraient être uniques
            $commonUniqueColumns = ['code', 'email', 'phone', 'reference'];
            foreach ($commonUniqueColumns as $col) {
                if (Schema::hasColumn($tableName, $col)) {
                    $config['columns'][] = $col;
                    break; // Prendre la première trouvée
                }
            }
        }

        // Si aucune colonne unique n'est trouvée, retourner null
        if (empty($config['columns'])) {
            return null;
        }

        return $config;
    }

    /**
     * Trouve les doublons dans une table
     */
    private function findDuplicates(string $tableName, array $config): array
    {
        $columns = $config['columns'];
        
        // Vérifier que les colonnes existent
        foreach ($columns as $col) {
            if (!Schema::hasColumn($tableName, $col)) {
                return ['count' => 0, 'groups' => []];
            }
        }

        $totalDuplicates = 0;
        $duplicateGroups = [];

        // Construire la requête pour trouver les doublons basés sur toutes les colonnes
        $query = DB::table($tableName)
            ->select($columns)
            ->selectRaw('COUNT(*) as count')
            ->groupBy($columns)
            ->havingRaw('COUNT(*) > 1');

        // Ajouter whereNotNull pour chaque colonne
        foreach ($columns as $col) {
            $query->whereNotNull($col);
        }

        $duplicateGroupsData = $query->get();

        foreach ($duplicateGroupsData as $dupGroup) {
            // Construire la requête pour récupérer tous les enregistrements avec ces valeurs
            $recordsQuery = DB::table($tableName);
            foreach ($columns as $col) {
                $recordsQuery->where($col, $dupGroup->$col);
            }
            
            $records = $recordsQuery->orderBy('created_at', 'desc')->get();

            if ($records->count() > 1) {
                $count = $records->count() - 1; // -1 car on garde un enregistrement
                $totalDuplicates += $count;
                
                $values = [];
                foreach ($columns as $col) {
                    $values[$col] = $dupGroup->$col;
                }
                
                $duplicateGroups[] = [
                    'values' => $values,
                    'records' => $records,
                    'count' => $count
                ];
            }
        }

        return [
            'count' => $totalDuplicates,
            'groups' => $duplicateGroups
        ];
    }

    /**
     * Nettoie les doublons en gardant le plus récent ou le plus ancien
     */
    private function cleanDuplicates(string $tableName, array $config, array $duplicates): int
    {
        $keep = $config['keep'] ?? 'newest';
        $totalCleaned = 0;

        foreach ($duplicates['groups'] as $group) {
            $records = $group['records'];
            
            if ($records->count() <= 1) {
                continue;
            }
            
            if ($keep === 'newest') {
                // Garder le premier (le plus récent car trié par created_at desc)
                $toKeep = $records->first();
                $toDelete = $records->slice(1);
            } else {
                // Garder le dernier (le plus ancien)
                $toKeep = $records->last();
                $toDelete = $records->slice(0, -1);
            }

            // Supprimer les doublons
            foreach ($toDelete as $record) {
                try {
                    DB::table($tableName)->where('id', $record->id)->delete();
                    $totalCleaned++;
                } catch (\Exception $e) {
                    $this->warn("      ⚠️  Erreur lors de la suppression de l'ID {$record->id}: {$e->getMessage()}");
                }
            }
        }

        return $totalCleaned;
    }
}
