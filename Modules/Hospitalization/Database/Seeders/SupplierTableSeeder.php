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
        DB::table('suppliers')->delete();

        Supplier::create([
            'id' => 1,
            'name' => 'Sobap',
            'email' => 'sobap@gmail.com',
            'dial_code' => '229',
            'phone_number' => 61524876,
            'address' => "Adresse Sobap Bénin",
            'profit_margin' => 50,
            'user_id' => 1,
        ]);  
        Supplier::create([
            'id' => 2,
            'name' => 'Dépôt Répartiteur',
            'email' => 'depotrepartiteur@gmail.com',
            'dial_code' => '229',
            'phone_number' => 63857412,
            'address' => "Adresse Dépôt Répartiteur",
            'profit_margin' => 40,
            'user_id' => 1,
        ]);  
        Supplier::create([
            'id' => 3,
            'name' => 'Bureau De Zone',
            'email' => 'bureaudezone@gmail.com',
            'dial_code' => '229',
            'phone_number' => 99152574,
            'address' => "Adresse Bureau De Zone",
            'profit_margin' => 10,
            'user_id' => 1,
        ]);  
    }
}
