<?php

namespace Modules\Medicalservices\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class MedicalservicesDatabaseSeeder extends Seeder
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
            ConsultationRecordTableSeeder::class,
        ]);

        Model::reguard();
    }
}
