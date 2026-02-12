<?php

namespace App\Console\Commands;

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\Hospitalization\Entities\Room;
use Modules\Administration\Entities\Service;

class AssignServicesToRoomsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rooms:assign-services 
                            {--hospital-id= : ID de l\'hôpital spécifique (optionnel)}
                            {--service-id= : ID du service à assigner (optionnel, sinon assigne le premier service disponible)}
                            {--force : Forcer l\'exécution sans confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assigne les services_id aux chambres (rooms) pour tous les tenants ou un tenant spécifique';

    protected $tenantConnectionService;

    public function __construct(TenantConnectionService $tenantConnectionService)
    {
        parent::__construct();
        $this->tenantConnectionService = $tenantConnectionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hospitalId = $this->option('hospital-id');
        $serviceId = $this->option('service-id');
        $force = $this->option('force');

        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║  Assignation des services aux chambres                     ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Déterminer les hôpitaux à traiter
        if ($hospitalId) {
            $hospitals = Hospital::where('id', $hospitalId)->get();
        } else {
            $hospitals = Hospital::all();
        }

        if ($hospitals->isEmpty()) {
            $this->error('❌ Aucun hôpital actif trouvé.');
            return Command::FAILURE;
        }

        if (!$force) {
            $this->warn("⚠️  Cette opération va assigner des services aux chambres pour " . $hospitals->count() . " hôpital(s).");
            if (!$this->confirm('Continuer ?', false)) {
                return Command::SUCCESS;
            }
        }

        $this->newLine();

        $results = [];
        $totalRooms = 0;
        $totalUpdated = 0;

        foreach ($hospitals as $hospital) {
            $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("🏥 Hôpital : {$hospital->name} (ID: {$hospital->id})");
            $this->line("   - Base de données : {$hospital->database_name}");

            try {
                // Connecter au tenant
                $this->tenantConnectionService->connect($hospital);

                // Récupérer les services disponibles
                $services = Service::all();
                
                if ($services->isEmpty()) {
                    $this->warn("   ⚠️  Aucun service trouvé dans cette base de données");
                    $results[] = [
                        'hospital' => $hospital->name,
                        'status' => 'skipped',
                        'message' => 'Aucun service disponible'
                    ];
                    continue;
                }

                // Déterminer le service à assigner
                $targetService = null;
                if ($serviceId) {
                    $targetService = $services->find($serviceId);
                    if (!$targetService) {
                        $this->warn("   ⚠️  Service ID {$serviceId} non trouvé, utilisation du premier service disponible");
                        $targetService = $services->first();
                    }
                } else {
                    $targetService = $services->first();
                }

                $this->line("   - Service assigné : {$targetService->name} (ID: {$targetService->id})");

                // Récupérer les chambres sans service
                $roomsWithoutService = Room::whereNull('services_id')->get();
                $allRooms = Room::all();
                
                $this->line("   - Chambres totales : {$allRooms->count()}");
                $this->line("   - Chambres sans service : {$roomsWithoutService->count()}");

                if ($roomsWithoutService->isEmpty()) {
                    $this->info("   ✅ Toutes les chambres ont déjà un service assigné");
                    $results[] = [
                        'hospital' => $hospital->name,
                        'status' => 'success',
                        'rooms_updated' => 0,
                        'total_rooms' => $allRooms->count()
                    ];
                    continue;
                }

                // Assigner le service
                $updated = Room::whereNull('services_id')
                    ->update(['services_id' => $targetService->id]);

                $totalRooms += $allRooms->count();
                $totalUpdated += $updated;

                $this->info("   ✅ {$updated} chambre(s) mise(s) à jour");

                $results[] = [
                    'hospital' => $hospital->name,
                    'status' => 'success',
                    'rooms_updated' => $updated,
                    'total_rooms' => $allRooms->count(),
                    'service' => $targetService->name
                ];

            } catch (\Exception $e) {
                $this->error("   ❌ Erreur : {$e->getMessage()}");
                $results[] = [
                    'hospital' => $hospital->name,
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }

        // Afficher le résumé
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════════════╗');
        $this->info('║                    RÉSULTATS                              ║');
        $this->info('╚══════════════════════════════════════════════════════════════╝');
        $this->newLine();

        $headers = ['Hôpital', 'Statut', 'Chambres mises à jour', 'Total chambres', 'Service'];
        $rows = [];

        foreach ($results as $result) {
            $rows[] = [
                $result['hospital'],
                $result['status'] === 'success' ? '✅ Succès' : ($result['status'] === 'skipped' ? '⚠️ Ignoré' : '❌ Erreur'),
                $result['rooms_updated'] ?? 'N/A',
                $result['total_rooms'] ?? 'N/A',
                $result['service'] ?? ($result['message'] ?? 'N/A')
            ];
        }

        $this->table($headers, $rows);

        $this->newLine();
        $this->info("Total chambres mises à jour : {$totalUpdated} sur {$totalRooms}");

        return Command::SUCCESS;
    }
}
