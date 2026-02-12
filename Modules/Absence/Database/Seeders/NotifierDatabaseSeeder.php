<?php

namespace Modules\Absence\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class AbsenceDatabaseSeeder extends Seeder
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
            TypeVacationSeeder::class,
            AbsentTableSeeder::class,
        ]);

        Model::reguard();
    }
}
