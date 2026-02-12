<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use App\Core\Services\TenantProvisioningService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Commande pour exécuter les seeders sur TOUS les tenants
 * 
 * @package App\Core\Console\Commands
 */
class TenantSeedAllCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:seed-all 
                            {--class= : Classe de seeder spécifique à exécuter}
                            {--force : Forcer l\'exécution sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exécute les seeders pour TOUS les tenants actifs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $seederClass = $this->option('class');
        $force = $this->option('force');

        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║  Exécution des seeders pour TOUS les tenants               ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        if ($seederClass) {
            $this->warn("⚠️  Seeder spécifique : {$seederClass}");
            $this->newLine();
        }

        if (!$force) {
            if (!$this->confirm('⚠️  Cette opération va exécuter les seeders sur TOUS les tenants actifs. Continuer ?', false)) {
                $this->info('Opération annulée.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();

        $hospitals = Hospital::active()->get();
        $results = [];
        $connectionService = app(TenantConnectionService::class);
        $provisioningService = app(TenantProvisioningService::class);

        foreach ($hospitals as $hospital) {
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
            $this->line("   - Base de données : {$hospital->database_name}");

            try {
                // Exécuter les seeders via le service de provisioning
                // Le service gère la connexion et la déconnexion automatiquement
                // On passe la commande actuelle pour que les seeders puissent afficher des messages
                if ($seederClass) {
                    // Nettoyer les backslashes doubles pour le format Laravel
                    $cleanSeederClass = str_replace('\\\\', '\\', $seederClass);
                    $provisioningService->seed($hospital, $cleanSeederClass, $this);
                } else {
                    $provisioningService->seed($hospital, null, $this);
                }

                $results[] = [
                    'hospital_id' => $hospital->id,
                    'hospital_name' => $hospital->name,
                    'database_name' => $hospital->database_name,
                    'success' => true,
                ];

                $this->info("   ✅ Seeders exécutés avec succès");
            } catch (\Exception $e) {
                Log::error("Erreur lors de l'exécution des seeders pour l'hôpital {$hospital->id}: " . $e->getMessage());
                
                $results[] = [
                    'hospital_id' => $hospital->id,
                    'hospital_name' => $hospital->name,
                    'database_name' => $hospital->database_name,
                    'success' => false,
                    'error' => $e->getMessage(),
                ];

                $this->error("   ❌ Erreur : {$e->getMessage()}");
            }
            // Note: La déconnexion est gérée par TenantProvisioningService::seed()
        }

        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                    RÉSULTATS                                ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $errorCount = count(array_filter($results, fn($r) => !$r['success']));

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

        if ($errorCount > 0) {
            $this->error("❌ Erreurs : {$errorCount}");
            $this->newLine();
            $this->warn("Détails des erreurs :");
            foreach ($results as $result) {
                if (!$result['success']) {
                    $this->error("  - {$result['hospital_name']} (ID: {$result['hospital_id']}) : {$result['error']}");
                }
            }
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('✅ Tous les seeders ont été exécutés avec succès sur tous les tenants !');
        return Command::SUCCESS;
    }
}
