<?php

namespace Modules\Stock\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Entities\Category;


class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        // Il suffit de créer les catégories sans hospital_id
        
        $categories = [
            ['name' => 'COMPRIME'],
            ['name' => 'GELLULE'],
            ['name' => 'GOUTTELETTE'],
            ['name' => 'INJECTABLE'],
            ['name' => 'POMMADE'],
            ['name' => 'PRODUIT ALIMENTAIRE'],
            ['name' => 'SIROP'],
            ['name' => 'SUPPOSITOIRE'],
            ['name' => 'AUTRES'],
            ['name' => 'CONSOMMABLE'],
            ['name' => 'SOLUTION'],
        ];

        $user = \Modules\Acl\Entities\User::first();
        
        foreach ($categories as $categoryData) {
            Category::updateOrCreate(
                [
                    'name' => $categoryData['name'],
                ],
                [
                    'user_id' => $user ? $user->id : null,
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'is_synced' => false,
                ]
            );
        }

        $this->command->info('✅ Catégories créées.');
    }
}
