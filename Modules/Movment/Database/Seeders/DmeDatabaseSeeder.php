<?php

namespace Modules\Movment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DmeDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->command->info('🌱 Démarrage des seeders DME...');
        
        $this->call([
            \Modules\Movment\Database\Seeders\AntecedentSeeder::class,
            \Modules\Movment\Database\Seeders\AllergieSeeder::class,
            \Modules\Movment\Database\Seeders\ClinicalObservationSeeder::class,
            \Modules\Movment\Database\Seeders\VaccinationSeeder::class,
            \Modules\Movment\Database\Seeders\PrescriptionSeeder::class,
            \Modules\Movment\Database\Seeders\DmeDocumentSeeder::class,
        ]);

        Model::reguard();
        
        $this->command->info('✅ Seeders DME terminés.');
    }
}
