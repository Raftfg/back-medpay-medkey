<?php

namespace App\Core\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Commande pour exécuter les seeders d'un tenant spécifique
 * 
 * @package App\Core\Console\Commands
 */
class TenantSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:seed 
                            {hospital_id : ID de l\'hôpital (tenant)}
                            {--class= : Classe de seeder spécifique à exécuter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exécute les seeders pour un tenant (hôpital) spécifique';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $hospitalId = $this->argument('hospital_id');
        $seederClass = $this->option('class');

        // Récupérer l'hôpital
        $hospital = Hospital::find($hospitalId);
        
        if (!$hospital) {
            $this->error("❌ Hôpital avec l'ID {$hospitalId} introuvable.");
            return Command::FAILURE;
        }

        $this->info("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
        $this->line("   - Domaine : {$hospital->domain}");
        $this->line("   - Base de données : {$hospital->database_name}");

        // Connecter au tenant
        try {
            $connectionService = app(TenantConnectionService::class);
            $connectionService->connect($hospital);
            
            $this->info("✅ Connecté à la base de données tenant");
        } catch (\Exception $e) {
            $this->error("❌ Erreur de connexion : {$e->getMessage()}");
            return Command::FAILURE;
        }

        // Exécuter les seeders
        try {
            $this->newLine();
            $this->info("🌱 Exécution des seeders...");

            $params = [
                '--database' => 'tenant',
                '--force' => true,
            ];

            if ($seederClass) {
                $params['--class'] = $seederClass;
            }

            Artisan::call('db:seed', $params);

            $this->info("✅ Seeders exécutés avec succès !");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de l'exécution des seeders :");
            $this->error($e->getMessage());
            
            if ($this->getOutput()->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        } finally {
            // Déconnecter
            app(TenantConnectionService::class)->disconnect();
        }
    }
}
