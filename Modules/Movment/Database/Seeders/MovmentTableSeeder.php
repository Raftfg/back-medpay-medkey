<?php

namespace Modules\Movment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Movment\Entities\Movment;
use App\Core\Models\Hospital;
use Modules\Patient\Entities\Patiente;
use Modules\Administration\Entities\Service;
use Illuminate\Support\Str;

class MovmentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        // Récupérer l'hôpital courant pour générer des IEP uniques
        $tenantService = app(\App\Core\Services\TenantConnectionService::class);
        $hospital = $tenantService->getCurrentHospital();
        
        // Récupérer les patients et services (sans hospital_id car on est déjà dans la base tenant)
        $patients = Patiente::limit(5)->get();
        $services = Service::limit(3)->get();
        
        if ($patients->isEmpty() || $services->isEmpty()) {
            $this->command->warn("⚠️  Données de référence manquantes (patients ou services). Veuillez d'abord exécuter les seeders de référence.");
            return;
        }

        // Créer quelques mouvements de test
        foreach ($patients as $index => $patient) {
            // Générer un IEP unique sous forme d'entier
            // Format: hospital_id * 10000 + index (ex: 1*10000+1 = 10001, 2*10000+1 = 20001)
            $hospitalId = $hospital ? $hospital->id : 1;
            $iep = (int) ($hospitalId * 10000 + ($index + 1));
            
            // Récupérer un service aléatoire
            $service = $services->random();
            
            Movment::updateOrCreate(
                [
                    'iep' => $iep,
                ],
                [
                    'uuid' => Str::uuid(),
                    'patients_id' => $patient->id,
                    'ipp' => $patient->ipp,
                    'iep' => $iep,
                    'active_services_code' => $service->code ?? $service->name, // Utiliser le code ou le nom du service
                    'arrivaldate' => now()->subDays(rand(1, 30)),
                    'releasedate' => null, // En cours
                    'is_synced' => false,
                ]
            );
        }

        $this->command->info('✅ Mouvements créés.');
    }
}
