<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Models\HospitalModule;
use App\Core\Services\TenantProvisioningService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Commande pour créer un hôpital (tenant)
 * 
 * Cette commande crée un nouvel hôpital dans la base CORE.
 * 
 * @package App\Core\Console\Commands
 */
class CreateHospitalCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hospital:create 
                            {name : Nom de l\'hôpital}
                            {--domain= : Domaine (ex: hopital-central.medkey.com)}
                            {--database= : Nom de la base de données (ex: medkey_hospital_1)}
                            {--host=127.0.0.1 : Host de la base de données}
                            {--port=3306 : Port de la base de données}
                            {--status=provisioning : Statut (active, inactive, suspended, provisioning)}
                            {--email= : Email de l\'hôpital}
                            {--phone= : Téléphone de l\'hôpital}
                            {--address= : Adresse de l\'hôpital}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crée un nouvel hôpital (tenant) dans la base CORE';

    protected ?TenantProvisioningService $provisioningService = null;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        
        // Générer le domaine si non fourni
        $domain = $this->option('domain');
        if (!$domain) {
            $slug = Str::slug($name);
            $domain = $slug . '.medkey.com';
            if (!$this->confirm("Domaine généré : {$domain}. Voulez-vous continuer ?", true)) {
                $this->info('Opération annulée.');
                return Command::SUCCESS;
            }
        }

        // Générer le nom de la base de données si non fourni
        $databaseName = $this->option('database');
        if (!$databaseName) {
            $slug = Str::slug($name, '_');
            $databaseName = 'medkey_' . $slug;
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

        $this->info("Création de l'hôpital : {$name}");
        $this->line("   - Domaine : {$domain}");
        $this->line("   - Base de données : {$databaseName}");
        $this->line("   - Statut : {$this->option('status')}");

        try {
            // Créer l'hôpital
            $hospital = Hospital::create([
                'name' => $name,
                'domain' => $domain,
                'database_name' => $databaseName,
                'database_host' => $this->option('host'),
                'database_port' => $this->option('port'),
                'status' => $this->option('status'),
                'email' => $this->option('email'),
                'phone' => $this->option('phone'),
                'address' => $this->option('address'),
            ]);

            $this->info("✅ Hôpital créé avec succès !");
            $this->line("   - ID : {$hospital->id}");
            $this->line("   - UUID : {$hospital->uuid}");
            $this->line("   - Slug : {$hospital->slug}");

            // Proposer le provisioning automatique
            if ($this->confirm('Voulez-vous provisionner cet hôpital maintenant ? (créer DB, migrations, modules)', false)) {
                $this->provisioningService = app(TenantProvisioningService::class);
                
                $this->newLine();
                $this->info("🚀 Provisionnement en cours...");

                $defaultModules = config('tenant.provisioning.default_modules', 'Acl,Administration,Patient,Payment');
                $modules = array_map('trim', explode(',', $defaultModules));

                $provisioningOptions = [
                    'create_database' => true,
                    'run_migrations' => true,
                    'activate_default_modules' => true,
                    'run_seeders' => $this->confirm('Voulez-vous exécuter les seeders ?', false),
                    'force' => false,
                ];

                try {
                    $results = $this->provisioningService->provision($hospital, $provisioningOptions);

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

                    $hospital->refresh();
                    $this->newLine();
                    $this->info("✅ Hôpital créé et provisionné avec succès !");
                    $this->line("   - Statut : {$hospital->status}");

                } catch (\Exception $e) {
                    $this->error("❌ Erreur lors du provisioning : {$e->getMessage()}");
                    $this->warn("💡 Vous pouvez provisionner manuellement avec : php artisan tenant:provision {$hospital->id}");
                }
            } else {
                // Proposer d'activer des modules par défaut
                if ($this->confirm('Voulez-vous activer des modules par défaut ?', true)) {
                    $defaultModules = config('tenant.provisioning.default_modules', 'Acl,Administration,Patient,Payment');
                    $modules = explode(',', $defaultModules);
                    
                    foreach ($modules as $moduleName) {
                        $moduleName = trim($moduleName);
                        if (!empty($moduleName)) {
                            HospitalModule::create([
                                'hospital_id' => $hospital->id,
                                'module_name' => $moduleName,
                                'is_enabled' => true,
                            ]);
                            $this->line("   ✅ Module '{$moduleName}' activé");
                        }
                    }
                }

                $this->newLine();
                $this->info("📝 Prochaines étapes :");
                $this->line("   1. Provisionner : php artisan tenant:provision {$hospital->id}");
                $this->line("   2. (Optionnel) Exécuter les seeders : php artisan tenant:seed {$hospital->id}");
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la création de l'hôpital :");
            $this->error($e->getMessage());
            
            if ($this->getOutput()->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }
}
