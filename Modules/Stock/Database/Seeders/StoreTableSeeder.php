<?php

namespace Modules\Stock\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Entities\Store;


class StoreTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        $stores = [
            [
                'code' => 'MAG-67205',
                'name' => 'Magasin Gros',
                'location' => 'Emplacement du magasin gros',
            ],
            [
                'code' => 'MAG-67505',
                'name' => 'Officine',
                'location' => 'Emplacement du magasin Officine',
            ],
        ];

        $user = \Modules\Acl\Entities\User::first();
        
        foreach ($stores as $storeData) {
            Store::updateOrCreate(
                [
                    'code' => $storeData['code'],
                ],
                array_merge($storeData, [
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'user_id' => $user ? $user->id : null,
                    'is_synced' => false,
                ])
            );
        }

        $this->command->info('✅ Magasins créés.');
    }
}
