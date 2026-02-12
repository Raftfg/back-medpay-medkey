<?php

namespace Modules\Stock\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Entities\Stock;


class StockTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        // Récupérer les magasins (sans hospital_id car on est déjà dans la base tenant)
        $stores = \Modules\Stock\Entities\Store::all();
        
        if ($stores->isEmpty()) {
            $this->command->warn("⚠️  Aucun magasin trouvé. Veuillez d'abord exécuter StoreTableSeeder.");
            return;
        }

        $user = \Modules\Acl\Entities\User::first();

        $stocks = [
            [
                'name' => 'Stock Gros',
                'store_id' => $stores->first()->id,
                'for_pharmacy_sale' => 0,
            ],
            [
                'name' => 'Stock Pharmacie',
                'store_id' => $stores->count() > 1 ? $stores->skip(1)->first()->id : $stores->first()->id,
                'for_pharmacy_sale' => 1,
            ],
            [
                'name' => 'Stock Arch',
                'store_id' => $stores->count() > 1 ? $stores->skip(1)->first()->id : $stores->first()->id,
                'for_pharmacy_sale' => 0,
            ],
        ];

        foreach ($stocks as $stockData) {
            Stock::updateOrCreate(
                [
                    'name' => $stockData['name'],
                ],
                array_merge($stockData, [
                    'uuid' => \Illuminate\Support\Str::uuid(),
                    'user_id' => $user ? $user->id : null,
                    'is_synced' => false,
                ])
            );
        }

        $this->command->info('✅ Stocks créés.');
    }
}
