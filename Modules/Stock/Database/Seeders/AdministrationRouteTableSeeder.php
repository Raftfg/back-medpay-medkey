<?php

namespace Modules\Stock\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Entities\AdministrationRoute;


class AdministrationRouteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        $routes = ['Auriculaire', 'Buccale', 'Nasale', 'Oculaire', 'Orale', 'Rectum', 'Vaginale'];

        $user = \Modules\Acl\Entities\User::first();
        
        foreach ($routes as $routeName) {
            AdministrationRoute::updateOrCreate(
                [
                    'name' => $routeName,
                ],
                [
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'user_id' => $user ? $user->id : null,
                    'is_synced' => false,
                ]
            );
        }

        $this->command->info('✅ Voies d\'administration créées.');
    }
}
