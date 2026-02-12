<?php

namespace Modules\Administration\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

use Modules\Administration\Entities\Insurance;
use Modules\Administration\Entities\ProductType;
use Modules\Administration\Entities\Pack;
use Modules\Acl\Entities\User;

class PackTableSeeder extends Seeder
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
        try {
            $data = loadJsonData("demo");
            
            if (!isset($data->packs) || empty($data->packs)) {
                $this->command->warn('⚠️  Aucune donnée de packs trouvée dans demo.json.');
                return;
            }

            // Récupérer une assurance (partagée entre tous les hôpitaux - pas de hospital_id)
            $insurance = Insurance::first();
            
            if (!$insurance) {
                $this->command->warn('⚠️  Aucune assurance trouvée. Veuillez d\'abord exécuter InsuranceTableSeeder.');
                return;
            }

            // Récupérer un utilisateur (sans hospital_id car on est déjà dans la base tenant)
            $user = User::first();
            
            if (!$user) {
                $this->command->warn("⚠️  Aucun utilisateur trouvé. Veuillez d'abord exécuter UserTableSeeder.");
                return;
            }

            // Créer les packs
            $packs = collect($data->packs)->map(
                function ($d) use ($user, $insurance) {
                    // Retirer product_types_id car la colonne n'existe pas dans la table
                    unset($d->product_types_id);
                    
                    return [
                        'designation' => $d->designation ?? '',
                        'percentage' => $d->percentage ?? 0,
                        'insurances_id' => $insurance->id,
                        'users_id' => $user->id,
                        'uuid' => Str::uuid(),
                        'is_synced' => false,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
            );

            // Utiliser updateOrCreate pour éviter les doublons
            foreach ($packs as $packData) {
                Pack::updateOrCreate(
                    [
                        'uuid' => $packData['uuid'],
                    ],
                    $packData
                );
            }

            $this->command->info('✅ Packs créés.');
        } catch (\Throwable $th) {
            $this->command->error('❌ Erreur lors de la création des packs: ' . $th->getMessage());
            throw $th;
        }
    }
}
