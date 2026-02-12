<?php

namespace Modules\Stock\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Entities\TypeProduct;


class TypeProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        $types = ['Drugs', 'Consumables', 'Notebooks and cards'];

        $user = \Modules\Acl\Entities\User::first();
        
        foreach ($types as $typeName) {
            TypeProduct::updateOrCreate(
                [
                    'name' => $typeName,
                ],
                [
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'user_id' => $user ? $user->id : null,
                    'is_synced' => false,
                ]
            );
        }

        $this->command->info('✅ Types de produits créés.');
    }
}
