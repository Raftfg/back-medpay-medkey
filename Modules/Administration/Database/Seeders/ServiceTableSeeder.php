<?php

namespace Modules\Administration\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

use Modules\Administration\Entities\Department;
use Modules\Administration\Entities\Service;
use App\Core\Models\Hospital;

class ServiceTableSeeder extends Seeder
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
        
        if (!isset($data->services) || empty($data->services)) {
            $this->command->warn('⚠️  Aucune donnée de services trouvée dans le fichier JSON.');
            return;
        }

        // Récupérer un département (sans hospital_id car on est déjà dans la base tenant)
        $department = Department::first();
        
        // Si aucun département n'existe encore, en créer un par défaut pour permettre la création des services
        if (!$department) {
            $this->command->warn("⚠️  Aucun département trouvé. Création d'un département par défaut 'Général'.");
            $department = Department::create([
                'name' => 'Général',
                'description' => 'Département par défaut pour les services.',
            ]);
        }

        DB::beginTransaction();

        try {
            $services = collect($data->services)->map(
                function ($d) use ($department) {
                    return [
                        'name' => $d->name ?? '',
                        'description' => $d->description ?? '',
                        'code' => $d->code ?? null,
                        'departments_id' => $department->id,
                        'uuid' => (string) Str::uuid(),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
            );

            // Utiliser updateOrCreate pour éviter les doublons
            foreach ($services as $serviceData) {
                Service::updateOrCreate(
                    [
                        'name' => $serviceData['name'],
                    ],
                    $serviceData
                );
            }

            DB::commit();
            $this->command->info('✅ Services créés/mis à jour.');

        } catch (\Throwable $th) {
            DB::rollBack();
            $this->command->error('❌ Erreur lors de l\'exécution de ServiceTableSeeder: ' . $th->getMessage());
            throw $th;
        } finally {
            Model::reguard();
        }
    }
}
