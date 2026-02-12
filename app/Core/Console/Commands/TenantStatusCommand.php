<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Services\TenantProvisioningService;
use Illuminate\Console\Command;

/**
 * Commande pour afficher le statut de provisioning d'un tenant
 * 
 * @package App\Core\Console\Commands
 */
class TenantStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:status 
                            {hospital_id? : ID de l\'hôpital (optionnel, affiche tous si non fourni)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Affiche le statut de provisioning d\'un ou plusieurs tenants';

    protected TenantProvisioningService $provisioningService;

    /**
     * Create a new command instance.
     *
     * @param TenantProvisioningService $provisioningService
     * @return void
     */
    public function __construct(TenantProvisioningService $provisioningService)
    {
        parent::__construct();
        $this->provisioningService = $provisioningService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $hospitalId = $this->argument('hospital_id');

        if ($hospitalId) {
            // Afficher le statut d'un hôpital spécifique
            $hospital = Hospital::find($hospitalId);
            
            if (!$hospital) {
                $this->error("❌ Hôpital avec l'ID {$hospitalId} introuvable.");
                return Command::FAILURE;
            }

            $this->displayHospitalStatus($hospital);
        } else {
            // Afficher le statut de tous les hôpitaux
            $hospitals = Hospital::all();

            if ($hospitals->isEmpty()) {
                $this->warn("Aucun hôpital trouvé.");
                return Command::SUCCESS;
            }

            $this->info("╔══════════════════════════════════════════════════════════════╗");
            $this->info("║  Statut de Provisionnement - Tous les Hôpitaux            ║");
            $this->info("╚══════════════════════════════════════════════════════════════╝\n");

            $table = [];
            foreach ($hospitals as $hospital) {
                $status = $this->provisioningService->getProvisioningStatus($hospital);
                
                $table[] = [
                    'ID' => $hospital->id,
                    'Nom' => $hospital->name,
                    'Domaine' => $hospital->domain,
                    'Statut' => $hospital->status,
                    'DB' => $status['database_exists'] ? '✅' : '❌',
                    'Migrations' => $status['migrations_count'],
                    'Modules' => $status['modules_count'],
                    'Provisionné' => $status['is_provisioned'] ? '✅' : '❌',
                ];
            }

            $this->table(
                ['ID', 'Nom', 'Domaine', 'Statut', 'DB', 'Migrations', 'Modules', 'Provisionné'],
                $table
            );
        }

        return Command::SUCCESS;
    }

    /**
     * Affiche le statut détaillé d'un hôpital
     *
     * @param Hospital $hospital
     * @return void
     */
    protected function displayHospitalStatus(Hospital $hospital): void
    {
        $this->info("╔══════════════════════════════════════════════════════════════╗");
        $this->info("║  Statut de Provisionnement                                  ║");
        $this->info("╚══════════════════════════════════════════════════════════════╝\n");

        $this->line("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
        $this->line("   - Domaine : {$hospital->domain}");
        $this->line("   - Base de données : {$hospital->database_name}");
        $this->line("   - Statut : {$hospital->status}");
        $this->line("   - Créé le : {$hospital->created_at}");
        if ($hospital->provisioned_at) {
            $this->line("   - Provisionné le : {$hospital->provisioned_at}");
        }

        $status = $this->provisioningService->getProvisioningStatus($hospital);

        $this->newLine();
        $this->info("📊 Détails du Provisionnement :");
        $this->line("   - Base de données : " . ($status['database_exists'] ? '✅ Existe' : '❌ N\'existe pas'));
        $this->line("   - Migrations exécutées : {$status['migrations_count']}");
        $this->line("   - Modules activés : {$status['modules_count']}");
        $this->line("   - Provisionné : " . ($status['is_provisioned'] ? '✅ Oui' : '❌ Non'));

        // Afficher les modules activés
        if ($status['modules_count'] > 0) {
            $modules = \App\Core\Models\HospitalModule::where('hospital_id', $hospital->id)
                ->where('is_enabled', true)
                ->pluck('module_name')
                ->toArray();
            
            $this->newLine();
            $this->info("📦 Modules activés :");
            foreach ($modules as $module) {
                $this->line("   - {$module}");
            }
        }

        $this->newLine();
        if (!$status['is_provisioned']) {
            $this->warn("💡 Pour provisionner cet hôpital :");
            $this->line("   php artisan tenant:provision {$hospital->id}");
        }
    }
}
