<?php

namespace Modules\Administration\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Modules\Administration\Entities\MedicalAct;
use Modules\Administration\Entities\TypeMedicalActs;
use Modules\Administration\Entities\Service;
use App\Core\Models\Hospital;

class MedicalActTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        $data = loadJsonData("demo");
        
        if (!isset($data->medical_acts) || empty($data->medical_acts)) {
            $this->command->warn('⚠️  Aucune donnée d\'actes médicaux trouvée dans le fichier JSON.');
            return;
        }

        // Récupérer le type d'acte médical (partagé entre tous les hôpitaux - pas de hospital_id)
        $typeMedicalActs = TypeMedicalActs::first();
        
        if (!$typeMedicalActs) {
            $this->command->warn('⚠️  Aucun type d\'acte médical trouvé. Veuillez d\'abord exécuter TypeMedicalActsTableSeeder.');
            return;
        }

        // Récupérer un service (sans hospital_id car on est déjà dans la base tenant)
        $service = Service::first();
        
        if (!$service) {
            $this->command->warn("⚠️  Aucun service trouvé. Veuillez d'abord exécuter ServiceTableSeeder.");
            return;
        }

        DB::beginTransaction();

        try {
            $medical_acts = collect($data->medical_acts)->map(
                function ($d) use ($service, $typeMedicalActs) {
                    return [
                        'code' => $d->code ?? '',
                        'designation' => $d->designation ?? '',
                        'description' => $d->description ?? '',
                        'price' => $d->price ?? 0,
                        'services_id' => $service->id,
                        'type_medical_acts_id' => $typeMedicalActs->id,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
            );

            // Utiliser updateOrCreate pour éviter les doublons
            foreach ($medical_acts as $medicalActData) {
                MedicalAct::updateOrCreate(
                    [
                        'code' => $medicalActData['code'],
                    ],
                    $medicalActData
                );
            }

            DB::commit();
            $this->command->info('✅ Actes médicaux créés/mis à jour.');

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->command->error('❌ Erreur lors de l\'exécution de MedicalActTableSeeder: ' . $th->getMessage());
            throw $th;
        } finally {
            Model::reguard();
        }
    }
}
