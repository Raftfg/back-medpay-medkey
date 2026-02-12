<?php

namespace Modules\Stock\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Entities\Supplier;


class SupplierTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        $suppliers = [
            [
                'name' => 'Sobap',
                'email' => 'sobap@gmail.com',
                'dial_code' => '229',
                'phone_number' => '61524876',
                'address' => "Adresse Sobap Bénin",
                'profit_margin' => 50,
            ],
            [
                'name' => 'Dépôt Répartiteur',
                'email' => 'depotrepartiteur@gmail.com',
                'dial_code' => '229',
                'phone_number' => '63857412',
                'address' => "Adresse Dépôt Répartiteur",
                'profit_margin' => 40,
            ],
            [
                'name' => 'Bureau De Zone',
                'email' => 'bureaudezone@gmail.com',
                'dial_code' => '229',
                'phone_number' => '99152574',
                'address' => "Adresse Bureau De Zone",
                'profit_margin' => 10,
            ],
        ];

        $user = \Modules\Acl\Entities\User::first();
        
        foreach ($suppliers as $supplierData) {
            Supplier::updateOrCreate(
                [
                    'name' => $supplierData['name'],
                ],
                array_merge($supplierData, [
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'user_id' => $user ? $user->id : null,
                    'is_synced' => false,
                ])
            );
        }

        $this->command->info('✅ Fournisseurs créés.');
    }
}
