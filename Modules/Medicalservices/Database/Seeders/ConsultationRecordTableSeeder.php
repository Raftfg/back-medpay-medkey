<?php

namespace Modules\Medicalservices\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Medicalservices\Entities\ConsultationRecord;
use App\Core\Models\Hospital;
use Modules\Movment\Entities\Movment;
use Illuminate\Support\Str;

class ConsultationRecordTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        // Récupérer les mouvements (sans hospital_id car on est déjà dans la base tenant)
        $movments = Movment::limit(5)->get();
        
        if ($movments->isEmpty()) {
            $this->command->warn("⚠️  Aucun mouvement trouvé. Veuillez d'abord exécuter MovmentTableSeeder.");
            return;
        }

        // Créer quelques dossiers de consultation de test
        foreach ($movments as $index => $movment) {
            // Récupérer le service associé au mouvement via active_services_code
            $service = \Modules\Administration\Entities\Service::where(function($query) use ($movment) {
                    $query->where('code', $movment->active_services_code)
                          ->orWhere('name', $movment->active_services_code);
                })
                ->first();
            
            if (!$service) {
                // Si aucun service trouvé, utiliser le premier service
                $service = \Modules\Administration\Entities\Service::first();
            }
            
            if (!$service) {
                $this->command->warn("⚠️  Aucun service trouvé pour le mouvement {$movment->id}. Ignoré.");
                continue;
            }
            
            ConsultationRecord::updateOrCreate(
                [
                    'movments_id' => $movment->id,
                ],
                [
                    'uuid' => Str::uuid(),
                    'movments_id' => $movment->id,
                    'services_id' => $service->id,
                    'measurement' => 'Tension: 120/80, Température: 37°C',
                    'complaint' => 'Douleur abdominale',
                    'exam' => 'Examen clinique normal',
                    'observation' => 'Patient stable',
                    'summary' => 'Consultation de routine',
                ]
            );
        }

        $this->command->info('✅ Dossiers de consultation créés.');
    }
}
