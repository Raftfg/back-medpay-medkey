<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Services\TenantProvisioningService;
use Illuminate\Console\Command;

/**
 * Commande pour provisionner un tenant existant
 * 
 * Cette commande provisionne un hôpital existant :
 * - Crée la base de données (si elle n'existe pas)
 * - Exécute les migrations
 * - Active les modules par défaut
 * - (Optionnel) Exécute les seeders
 * 
 * @package App\Core\Console\Commands
 */
class TenantProvisionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:provision 
                            {hospital_id : ID de l\'hôpital à provisionner}
                            {--seed : Exécuter les seeders après le provisioning}
                            {--force : Forcer la création même si la base existe}
                            {--modules= : Modules à activer (séparés par virgule)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provisionne un tenant (hôpital) existant (créer DB, migrations, modules)';

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

        // Récupérer l'hôpital
        $hospital = Hospital::find($hospitalId);
        
        if (!$hospital) {
            $this->error("❌ Hôpital avec l'ID {$hospitalId} introuvable.");
            return Command::FAILURE;
        }

        $this->info("╔══════════════════════════════════════════════════════════════╗");
        $this->info("║  Provisionnement d'un tenant (hôpital)                    ║");
        $this->info("╚══════════════════════════════════════════════════════════════╝\n");

        $this->line("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
        $this->line("   - Domaine : {$hospital->domain}");
        $this->line("   - Base de données : {$hospital->database_name}");
        $this->line("   - Statut actuel : {$hospital->status}");

        // Vérifier le statut actuel
        $status = $this->provisioningService->getProvisioningStatus($hospital);
        
        if ($status['is_provisioned'] && !$this->option('force')) {
            $this->warn("\n⚠️  Cet hôpital semble déjà être provisionné.");
            $this->line("   - Base de données : " . ($status['database_exists'] ? '✅ Existe' : '❌ N\'existe pas'));
            $this->line("   - Migrations : {$status['migrations_count']}");
            $this->line("   - Modules activés : {$status['modules_count']}");
            
            if (!$this->confirm('Voulez-vous continuer quand même ?', false)) {
                $this->info('Opération annulée.');
                return Command::SUCCESS;
            }
        }

        try {
            $this->newLine();
            $this->info("🚀 Démarrage du provisioning...");

            // Déterminer les modules à activer
            $modules = $this->option('modules');
            if ($modules) {
                $modules = array_map('trim', explode(',', $modules));
            } else {
                $defaultModules = config('tenant.provisioning.default_modules', 'Acl,Administration,Patient,Payment');
                $modules = array_map('trim', explode(',', $defaultModules));
            }

            $provisioningOptions = [
                'create_database' => true,
                'run_migrations' => true,
                'activate_default_modules' => true,
                'run_seeders' => $this->option('seed'),
                'force' => $this->option('force'),
            ];

            $results = $this->provisioningService->provision($hospital, $provisioningOptions);

            // Afficher les résultats
            $this->newLine();
            $this->info("╔══════════════════════════════════════════════════════════════╗");
            $this->info("║  Résultats du Provisionnement                              ║");
            $this->info("╚══════════════════════════════════════════════════════════════╝\n");
            
            if ($results['database_created']) {
                $this->info("   ✅ Base de données créée");
            }
            
            if ($results['migrations_executed']) {
                $this->info("   ✅ Migrations exécutées");
            }
            
            if ($results['modules_activated']) {
                $this->info("   ✅ Modules activés : " . implode(', ', $results['modules'] ?? []));
            }
            
            if ($results['seeders_executed']) {
                $this->info("   ✅ Seeders exécutés");
            }

            if (!empty($results['errors'])) {
                $this->newLine();
                $this->warn("⚠️  Erreurs rencontrées :");
                foreach ($results['errors'] as $error) {
                    $this->error("   - {$error}");
                }
            }

            // Afficher le statut final
            $hospital->refresh();
            $finalStatus = $this->provisioningService->getProvisioningStatus($hospital);
            
            $this->newLine();
            $this->info("📊 Statut final :");
            $this->line("   - Statut : {$hospital->status}");
            $this->line("   - Base de données : " . ($finalStatus['database_exists'] ? '✅ Existe' : '❌ N\'existe pas'));
            $this->line("   - Migrations : {$finalStatus['migrations_count']}");
            $this->line("   - Modules activés : {$finalStatus['modules_count']}");
            $this->line("   - Provisionné : " . ($finalStatus['is_provisioned'] ? '✅ Oui' : '❌ Non'));

            $this->newLine();
            $this->info("✅ Provisionnement terminé avec succès !");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors du provisioning :");
            $this->error($e->getMessage());
            
            if ($this->getOutput()->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }
}
