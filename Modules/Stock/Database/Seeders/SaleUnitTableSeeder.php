<?php

namespace Modules\Stock\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Entities\SaleUnit;


class SaleUnitTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        $units = ['U', 'MG', 'ML', 'µg', 'g', 'MMOL', 'M', 'CM'];

        $user = \Modules\Acl\Entities\User::first();
        
        foreach ($units as $unitName) {
            SaleUnit::updateOrCreate(
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

        $this->command->info('✅ Unités de vente créées.');
    }
}
