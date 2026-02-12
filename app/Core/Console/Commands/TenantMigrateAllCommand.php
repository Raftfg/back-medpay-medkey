<?php

namespace App\Core\Console\Commands;

use App\Core\Services\TenantProvisioningService;
use Illuminate\Console\Command;

/**
 * Commande pour exécuter les migrations sur TOUS les tenants
 * 
 * IMPORTANT : Toute nouvelle migration doit être appliquée à TOUS les tenants,
 * pas seulement au tenant en cours. Utilisez cette commande pour cela.
 * 
 * @package App\Core\Console\Commands
 */
class TenantMigrateAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate-all 
                            {--path= : Chemin spécifique vers les migrations à exécuter}
                            {--force : Forcer l\'exécution sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exécute les migrations pour TOUS les tenants actifs (IMPORTANT : à utiliser pour toute nouvelle migration)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = $this->option('path');
        $force = $this->option('force');

        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║  Exécution des migrations pour TOUS les tenants            ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        if ($path) {
            $this->warn("⚠️  Chemin spécifique : {$path}");
            $this->newLine();
        }

        if (!$force) {
            if (!$this->confirm('⚠️  Cette opération va exécuter les migrations sur TOUS les tenants actifs. Continuer ?', false)) {
                $this->info('Opération annulée.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();

        $provisioningService = app(TenantProvisioningService::class);

        // Callback pour afficher la progression
        $progressCallback = function ($hospital, $status, $error = null) {
            switch ($status) {
                case 'start':
                    $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                    $this->info("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
                    $this->line("   - Base de données : {$hospital->database_name}");
                    break;
                case 'success':
                    $this->info("   ✅ Migrations exécutées avec succès");
                    break;
                case 'skipped':
                    $this->warn("   ⚠️  Ignoré : Base de données non créée ou table déjà existante");
                    break;
                case 'error':
                    $this->error("   ❌ Erreur : {$error}");
                    break;
            }
        };

        try {
            $results = $provisioningService->runMigrationsForAllTenants($path, $progressCallback);

            $this->newLine();
            $this->info('╔══════════════════════════════════════════════════════════════╗');
            $this->info('║                    RÉSULTATS                                ║');
            $this->info('╚══════════════════════════════════════════════════════════════╝');
            $this->newLine();

            $successCount = count(array_filter($results, fn($r) => $r['success'] && !isset($r['skipped'])));
            $skippedCount = count(array_filter($results, fn($r) => isset($r['skipped']) && $r['skipped']));
            $errorCount = count($results) - $successCount - $skippedCount;

            $this->table(
                ['ID', 'Hôpital', 'Base de données', 'Statut'],
                array_map(function ($result) {
                    return [
                        $result['hospital_id'],
                        $result['hospital_name'],
                        $result['database_name'],
                        $result['success'] ? '✅ Succès' : '❌ Erreur',
                    ];
                }, $results)
            );

            $this->newLine();
            $this->info("Total : " . count($results));
            $this->info("✅ Succès : {$successCount}");
            if ($skippedCount > 0) {
                $this->warn("⚠️  Ignorés (base non créée) : {$skippedCount}");
            }
            
            if ($errorCount > 0) {
                $this->error("❌ Erreurs : {$errorCount}");
                $this->newLine();
                $this->warn("Détails des erreurs :");
                foreach ($results as $result) {
                    if (!$result['success'] && !isset($result['skipped'])) {
                        $this->error("  - {$result['hospital_name']} (ID: {$result['hospital_id']}) : {$result['error']}");
                    }
                }
                return Command::FAILURE;
            }

            $this->newLine();
            $this->info('✅ Toutes les migrations ont été exécutées avec succès sur tous les tenants !');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de l'exécution des migrations :");
            $this->error($e->getMessage());
            
            if ($this->getOutput()->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }
}
