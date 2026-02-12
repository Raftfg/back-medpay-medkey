<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Services\TenantProvisioningService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Commande pour créer un nouveau tenant (hôpital) avec provisioning automatique
 * 
 * Cette commande crée un nouvel hôpital et le provisionne automatiquement :
 * - Crée l'entrée dans la base CORE
 * - Crée la base de données
 * - Exécute les migrations
 * - Active les modules par défaut
 * - (Optionnel) Exécute les seeders
 * 
 * @package App\Core\Console\Commands
 */
class TenantCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create 
                            {name : Nom de l\'hôpital}
                            {domain : Domaine (ex: hopital-central.medkey.com)}
                            {--database= : Nom de la base de données (auto-généré si non fourni)}
                            {--host=127.0.0.1 : Host de la base de données}
                            {--port=3306 : Port de la base de données}
                            {--provision : Provisionner automatiquement (créer DB, migrations, modules)}
                            {--seed : Exécuter les seeders après le provisioning}
                            {--force : Forcer la création même si la base existe}
                            {--modules= : Modules à activer (séparés par virgule, défaut: ceux de config)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crée un nouveau tenant (hôpital) avec provisioning automatique';

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
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        
        // Générer le nom de la base de données si non fourni
        $databaseName = $this->option('database');
        if (!$databaseName) {
            $slug = Str::slug($name, '_');
            $databaseName = config('tenant.database_prefix', 'medkey_') . $slug;
        }

        // Vérifier que le domaine n'existe pas déjà
        if (Hospital::where('domain', $domain)->exists()) {
            $this->error("❌ Un hôpital avec le domaine '{$domain}' existe déjà.");
            return Command::FAILURE;
        }

        // Vérifier que le nom de base n'existe pas déjà
        if (Hospital::where('database_name', $databaseName)->exists()) {
            $this->error("❌ Un hôpital avec la base de données '{$databaseName}' existe déjà.");
            return Command::FAILURE;
        }

        $this->info("╔══════════════════════════════════════════════════════════════╗");
        $this->info("║  Création d'un nouveau tenant (hôpital)                    ║");
        $this->info("╚══════════════════════════════════════════════════════════════╝\n");

        $this->line("📋 Informations :");
        $this->line("   - Nom : {$name}");
        $this->line("   - Domaine : {$domain}");
        $this->line("   - Base de données : {$databaseName}");
        $this->line("   - Host : {$this->option('host')}");
        $this->line("   - Port : {$this->option('port')}");

        try {
            // Créer l'hôpital dans CORE
            $this->info("\n📝 Création de l'hôpital dans la base CORE...");
            
            $hospital = Hospital::create([
                'name' => $name,
                'domain' => $domain,
                'slug' => Str::slug($name),
                'database_name' => $databaseName,
                'database_host' => $this->option('host'),
                'database_port' => $this->option('port'),
                'database_username' => config('database.connections.mysql.username'),
                'database_password' => config('database.connections.mysql.password'),
                'status' => $this->option('provision') ? 'provisioning' : 'inactive',
            ]);

            $this->info("✅ Hôpital créé avec succès !");
            $this->line("   - ID : {$hospital->id}");
            $this->line("   - UUID : {$hospital->uuid}");

            // Provisionner si demandé
            if ($this->option('provision')) {
                $this->newLine();
                $this->info("🚀 Provisionnement automatique...");

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
                $this->info("📊 Résultats du provisioning :");
                
                if ($results['database_created']) {
                    $this->line("   ✅ Base de données créée");
                }
                
                if ($results['migrations_executed']) {
                    $this->line("   ✅ Migrations exécutées");
                }
                
                if ($results['modules_activated']) {
                    $this->line("   ✅ Modules activés : " . implode(', ', $results['modules'] ?? []));
                }
                
                if ($results['seeders_executed']) {
                    $this->line("   ✅ Seeders exécutés");
                }

                if (!empty($results['errors'])) {
                    $this->newLine();
                    $this->warn("⚠️  Erreurs rencontrées :");
                    foreach ($results['errors'] as $error) {
                        $this->error("   - {$error}");
                    }
                }

                $this->newLine();
                $this->info("✅ Tenant créé et provisionné avec succès !");
                $this->line("   - Statut : {$hospital->fresh()->status}");
                $this->line("   - Provisionné le : {$hospital->fresh()->provisioned_at}");

            } else {
                $this->newLine();
                $this->info("📝 Prochaines étapes :");
                $this->line("   1. Créer la base de données : CREATE DATABASE `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                $this->line("   2. Provisionner : php artisan tenant:provision {$hospital->id}");
                $this->line("   3. (Optionnel) Exécuter les seeders : php artisan tenant:seed {$hospital->id}");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la création du tenant :");
            $this->error($e->getMessage());
            
            if ($this->getOutput()->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }
}
