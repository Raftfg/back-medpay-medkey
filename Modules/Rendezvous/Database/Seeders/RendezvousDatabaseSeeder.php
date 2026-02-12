<?php

namespace Modules\Rendezvous\Database\Seeders;

use Illuminate\Database\Seeder;

class RendezvousDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            DoctorAvailabilitySeeder::class,
            AppointmentSeeder::class,
        ]);
    }
}

