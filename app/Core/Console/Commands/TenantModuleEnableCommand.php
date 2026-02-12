<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Services\ModuleService;
use Illuminate\Console\Command;

/**
 * Commande pour activer un ou plusieurs modules pour un tenant
 * 
 * @package App\Core\Console\Commands
 */
class TenantModuleEnableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:module:enable 
                            {hospital_id : ID de l\'hôpital}
                            {modules : Noms des modules à activer (séparés par virgule)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Active un ou plusieurs modules pour un tenant (hôpital)';

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
        $modulesInput = $this->argument('modules');

        // Récupérer l'hôpital
        $hospital = Hospital::find($hospitalId);
        
        if (!$hospital) {
            $this->error("❌ Hôpital avec l'ID {$hospitalId} introuvable.");
            return Command::FAILURE;
        }

        // Parser les modules
        $moduleNames = array_map('trim', explode(',', $modulesInput));

        $this->info("╔══════════════════════════════════════════════════════════════╗");
        $this->info("║  Activation de Modules                                      ║");
        $this->info("╚══════════════════════════════════════════════════════════════╝\n");

        $this->line("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
        $this->line("📦 Modules à activer : " . implode(', ', $moduleNames));

        // Vérifier que les modules existent
        $availableModules = $this->moduleService->getAvailableModules();
        $invalidModules = array_diff($moduleNames, $availableModules);

        if (!empty($invalidModules)) {
            $this->warn("\n⚠️  Modules invalides détectés : " . implode(', ', $invalidModules));
            $this->line("📋 Modules disponibles : " . implode(', ', $availableModules));
            
            if (!$this->confirm('Voulez-vous continuer quand même ?', false)) {
                $this->info('Opération annulée.');
                return Command::SUCCESS;
            }
        }

        try {
            $this->newLine();
            $this->info("🚀 Activation des modules...");

            $activated = $this->moduleService->enableModules($hospital, $moduleNames);

            if (empty($activated)) {
                $this->error("❌ Aucun module n'a pu être activé.");
                return Command::FAILURE;
            }

            $this->newLine();
            $this->info("✅ Modules activés avec succès :");
            foreach ($activated as $module) {
                $this->line("   - {$module}");
            }

            // Afficher les modules non activés
            $failed = array_diff($moduleNames, $activated);
            if (!empty($failed)) {
                $this->newLine();
                $this->warn("⚠️  Modules non activés :");
                foreach ($failed as $module) {
                    $this->line("   - {$module}");
                }
            }

            // Afficher le statut final
            $this->newLine();
            $enabledModules = $this->moduleService->getEnabledModules($hospital);
            $this->info("📊 Modules actuellement activés pour cet hôpital :");
            $this->line("   " . (empty($enabledModules) ? 'Aucun' : implode(', ', $enabledModules)));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de l'activation des modules :");
            $this->error($e->getMessage());
            
            if ($this->getOutput()->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }
}
