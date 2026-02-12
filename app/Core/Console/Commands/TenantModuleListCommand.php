<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Services\ModuleService;
use Illuminate\Console\Command;

/**
 * Commande pour lister les modules d'un tenant
 * 
 * @package App\Core\Console\Commands
 */
class TenantModuleListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:module:list 
                            {hospital_id? : ID de l\'hôpital (optionnel, affiche tous si non fourni)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Liste les modules disponibles et leur statut pour un ou plusieurs tenants';

    protected ModuleService $moduleService;

    /**
     * Create a new command instance.
     *
     * @param ModuleService $moduleService
     * @return void
     */
    public function __construct(ModuleService $moduleService)
    {
        parent::__construct();
        $this->moduleService = $moduleService;
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
            // Afficher les modules d'un hôpital spécifique
            $hospital = Hospital::find($hospitalId);
            
            if (!$hospital) {
                $this->error("❌ Hôpital avec l'ID {$hospitalId} introuvable.");
                return Command::FAILURE;
            }

            $this->displayHospitalModules($hospital);
        } else {
            // Afficher les modules de tous les hôpitaux
            $hospitals = Hospital::all();

            if ($hospitals->isEmpty()) {
                $this->warn("Aucun hôpital trouvé.");
                return Command::SUCCESS;
            }

            $this->info("╔══════════════════════════════════════════════════════════════╗");
            $this->info("║  Modules par Hôpital                                        ║");
            $this->info("╚══════════════════════════════════════════════════════════════╝\n");

            foreach ($hospitals as $hospital) {
                $this->displayHospitalModules($hospital, false);
                $this->newLine();
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Affiche les modules d'un hôpital
     *
     * @param Hospital $hospital
     * @param bool $showHeader
     * @return void
     */
    protected function displayHospitalModules(Hospital $hospital, bool $showHeader = true): void
    {
        if ($showHeader) {
            $this->info("╔══════════════════════════════════════════════════════════════╗");
            $this->info("║  Modules de l'Hôpital                                       ║");
            $this->info("╚══════════════════════════════════════════════════════════════╝\n");
        }

        $this->line("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
        $this->line("   - Domaine : {$hospital->domain}");

        $modulesStatus = $this->moduleService->getModulesStatus($hospital);
        $availableModules = $this->moduleService->getAvailableModules();

        if (empty($availableModules)) {
            $this->warn("\n⚠️  Aucun module disponible trouvé.");
            return;
        }

        $this->newLine();
        $this->info("📦 Statut des Modules :");

        $table = [];
        foreach ($availableModules as $module) {
            $status = $modulesStatus[$module] ?? false;
            $table[] = [
                'Module' => $module,
                'Statut' => $status ? '✅ Activé' : '❌ Désactivé',
            ];
        }

        $this->table(['Module', 'Statut'], $table);

        // Afficher les modules activés
        $enabledModules = $this->moduleService->getEnabledModules($hospital);
        $this->newLine();
        $this->info("📊 Résumé :");
        $this->line("   - Modules disponibles : " . count($availableModules));
        $this->line("   - Modules activés : " . count($enabledModules));
        $this->line("   - Modules désactivés : " . (count($availableModules) - count($enabledModules)));

        if (!empty($enabledModules)) {
            $this->newLine();
            $this->info("✅ Modules activés :");
            foreach ($enabledModules as $module) {
                $this->line("   - {$module}");
            }
        }
    }
}
