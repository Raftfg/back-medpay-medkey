<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Commande avancée pour détecter les doublons avec critères personnalisés
 */
class FindDuplicatesAdvancedCommand extends Command
{
    protected $signature = 'tenant:find-duplicates-advanced 
                            {--hospital-id= : ID de l\'hôpital spécifique (optionnel)}
                            {--table= : Table spécifique à vérifier}
                            {--columns= : Colonnes pour détecter les doublons (séparées par virgule, ex: patients_id,date)}
                            {--clean : Nettoyer automatiquement les doublons}
                            {--dry-run : Mode simulation}
                            {--limit=100 : Limite d\'affichage des résultats}';

    protected $description = 'Détecte les doublons avec critères personnalisés dans les tables';

    public function handle()
    {
        $hospitalId = $this->option('hospital-id');
        $tableName = $this->option('table');
        $columnsStr = $this->option('columns');
        $clean = $this->option('clean');
        $dryRun = $this->option('dry-run');
        $limit = (int)$this->option('limit');

        if (!$tableName) {
            $this->error('❌ Vous devez spécifier une table avec --table=nom_table');
            return Command::FAILURE;
        }

        if (!$columnsStr) {
            $this->error('❌ Vous devez spécifier les colonnes avec --columns=col1,col2');
            return Command::FAILURE;
        }

        $columns = array_map('trim', explode(',', $columnsStr));

        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║  Détection avancée de doublons                             ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();
        $this->info("📋 Table : {$tableName}");
        $this->info("🔍 Colonnes : " . implode(', ', $columns));
        $this->newLine();

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

                if (!Schema::hasTable($tableName)) {
                    $this->warn("   ⚠️  La table '{$tableName}' n'existe pas dans cette base.");
                    continue;
                }

                // Vérifier que les colonnes existent
                foreach ($columns as $col) {
                    if (!Schema::hasColumn($tableName, $col)) {
                        $this->error("   ❌ La colonne '{$col}' n'existe pas dans la table '{$tableName}'.");
                        continue 2;
                    }
                }

                $this->line("   📋 Recherche des doublons...");

                // Trouver les doublons
                $duplicates = $this->findDuplicatesByColumns($tableName, $columns, $limit);

                if ($duplicates['count'] > 0) {
                    $this->warn("   ⚠️  {$duplicates['count']} doublon(s) détecté(s)");
                    $this->newLine();

                    // Afficher les détails
                    $displayed = 0;
                    foreach ($duplicates['groups'] as $group) {
                        if ($displayed >= $limit) {
                            $this->line("   ... (limite d'affichage atteinte)");
                            break;
                        }

                        $valuesStr = implode(' | ', array_map(function($k, $v) {
                            $val = is_null($v) ? 'NULL' : (string)$v;
                            return "{$k}: " . (strlen($val) > 40 ? substr($val, 0, 40) . '...' : $val);
                        }, array_keys($group['values']), $group['values']));

                        $this->line("   📌 {$valuesStr}");
                        $this->line("      → {$group['count']} enregistrement(s) en double");

                        // Afficher les IDs des doublons
                        $ids = $group['records']->pluck('id')->toArray();
                        $idsStr = implode(', ', array_slice($ids, 0, 10));
                        if (count($ids) > 10) {
                            $idsStr .= ' ... (+' . (count($ids) - 10) . ' autres)';
                        }
                        $this->line("      IDs: {$idsStr}");
                        $this->newLine();
                        $displayed++;
                    }

                    $totalDuplicates += $duplicates['count'];

                    if ($clean && !$dryRun) {
                        $cleaned = $this->cleanDuplicatesByColumns($tableName, $columns, $duplicates);
                        $this->info("   ✅ {$cleaned} doublon(s) supprimé(s)");
                        $totalCleaned += $cleaned;
                    } elseif ($dryRun) {
                        $this->line("   [DRY-RUN] {$duplicates['count']} doublon(s) seraient supprimés");
                    }
                } else {
                    $this->info("   ✅ Aucun doublon détecté avec ces critères");
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Erreur : {$e->getMessage()}");
                $this->error("   Trace : " . $e->getTraceAsString());
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
     * Trouve les doublons basés sur des colonnes spécifiques
     */
    private function findDuplicatesByColumns(string $tableName, array $columns, int $limit): array
    {
        $totalDuplicates = 0;
        $duplicateGroups = [];

        // Construire la requête pour trouver les doublons
        $query = DB::table($tableName)
            ->select($columns)
            ->selectRaw('COUNT(*) as count')
            ->groupBy($columns)
            ->havingRaw('COUNT(*) > 1');

        // Ajouter whereNotNull pour chaque colonne
        foreach ($columns as $col) {
            $query->whereNotNull($col);
        }

        $duplicateGroupsData = $query->limit($limit)->get();

        foreach ($duplicateGroupsData as $dupGroup) {
            // Récupérer tous les enregistrements avec ces valeurs
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
     * Nettoie les doublons en gardant le plus récent
     */
    private function cleanDuplicatesByColumns(string $tableName, array $columns, array $duplicates): int
    {
        $totalCleaned = 0;

        foreach ($duplicates['groups'] as $group) {
            $records = $group['records'];
            
            if ($records->count() <= 1) {
                continue;
            }
            
            // Garder le premier (le plus récent car trié par created_at desc)
            $toKeep = $records->first();
            $toDelete = $records->slice(1);

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
