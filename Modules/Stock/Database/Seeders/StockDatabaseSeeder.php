<?php

namespace Modules\Stock\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Stock\Entities\AdministrationRoute;

class StockDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        // Very important to run the TypeProductTableSeeder
        $this->call(TypeProductTableSeeder::class);
        $this->call(SaleUnitTableSeeder::class);
        $this->call(ConditioningUnitTableSeeder::class);
        $this->call(AdministrationRouteTableSeeder::class);
        $this->call(CategoryTableSeeder::class);
        $this->call(SupplierTableSeeder::class);
        $this->call(StoreTableSeeder::class);
        $this->call(StockTableSeeder::class);
        $this->call(ProductTableSeeder::class);

        Model::reguard();
    }
}
