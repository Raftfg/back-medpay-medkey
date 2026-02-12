<?php

namespace Modules\Movment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class MovmentDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call([
            MovmentTableSeeder::class,
            DmeDatabaseSeeder::class,
        ]);

        Model::reguard();
    }
}
