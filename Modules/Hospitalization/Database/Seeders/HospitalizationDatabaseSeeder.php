<?php

namespace Modules\Hospitalization\Database\Seeders;

use Illuminate\Database\Seeder;

class HospitalizationDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RoomTableSeeder::class,
            BedTableSeeder::class,
            ServiceDoctorsAndBedsSeeder::class, // Associe médecins et lits aux services
        ]);
    }
}
