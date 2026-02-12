<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Services\ModuleService;
use Illuminate\Console\Command;

/**
 * Commande pour désactiver un ou plusieurs modules pour un tenant
 * 
 * @package App\Core\Console\Commands
 */
class TenantModuleDisableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:module:disable 
                            {hospital_id : ID de l\'hôpital}
                            {modules : Noms des modules à désactiver (séparés par virgule)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Désactive un ou plusieurs modules pour un tenant (hôpital)';

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
        $this->info("║  Désactivation de Modules                                   ║");
        $this->info("╚══════════════════════════════════════════════════════════════╝\n");

        $this->line("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
        $this->line("📦 Modules à désactiver : " . implode(', ', $moduleNames));

        // Avertissement pour les modules critiques
        $criticalModules = ['Acl', 'Administration'];
        $criticalToDisable = array_intersect($moduleNames, $criticalModules);
        
        if (!empty($criticalToDisable)) {
            $this->warn("\n⚠️  ATTENTION : Vous êtes sur le point de désactiver des modules critiques :");
            foreach ($criticalToDisable as $module) {
                $this->line("   - {$module}");
            }
            
            if (!$this->confirm('Êtes-vous sûr de vouloir continuer ?', false)) {
                $this->info('Opération annulée.');
                return Command::SUCCESS;
            }
        }

        try {
            $this->newLine();
            $this->info("🚀 Désactivation des modules...");

            $deactivated = $this->moduleService->disableModules($hospital, $moduleNames);

            if (empty($deactivated)) {
                $this->error("❌ Aucun module n'a pu être désactivé.");
                return Command::FAILURE;
            }

            $this->newLine();
            $this->info("✅ Modules désactivés avec succès :");
            foreach ($deactivated as $module) {
                $this->line("   - {$module}");
            }

            // Afficher les modules non désactivés
            $failed = array_diff($moduleNames, $deactivated);
            if (!empty($failed)) {
                $this->newLine();
                $this->warn("⚠️  Modules non désactivés :");
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
            $this->error("❌ Erreur lors de la désactivation des modules :");
            $this->error($e->getMessage());
            
            if ($this->getOutput()->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }
}
