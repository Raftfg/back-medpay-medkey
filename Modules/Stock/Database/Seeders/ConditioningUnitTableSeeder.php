<?php

namespace Modules\Stock\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Entities\ConditioningUnit;


class ConditioningUnitTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        $units = ['Ampoule', 'Plaquette', 'Sachet', 'Carton', 'Autres', 'Boîte', 'Bouteille', 'Tube'];

        $user = \Modules\Acl\Entities\User::first();
        
        foreach ($units as $unitName) {
            ConditioningUnit::updateOrCreate(
                [
                    'name' => $unitName,
                ],
                [
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'user_id' => $user ? $user->id : null,
                    'is_synced' => false,
                ]
            );
        }

        $this->command->info('✅ Unités de conditionnement créées.');
    }
}
