<?php

namespace Modules\Administration\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class AdministrationDatabaseSeeder extends Seeder
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

             PaysSeederTableSeeder::class,
             DepartementTableSeeder::class,
             CommuneTableSeeder::class,
             ArrondissementTableSeeder::class,

            // NOTE: HospitalTableSeeder retiré - Les hôpitaux sont maintenant dans la base CORE
            // et sont créés via la commande hospital:create ou tenant:migrate-existing

            InsuranceTableSeeder::class,
            ProductTypeTableSeeder::class,
            PackTableSeeder::class,

            DepartmentTableSeeder::class,
            ServiceTableSeeder::class,

            TypeMedicalActsTableSeeder::class,
            MedicalActTableSeeder::class,
            
            // NOTE: HospitalSettingTableSeeder retiré - À créer si nécessaire
            // Les paramètres d'hôpital peuvent être gérés via l'interface ou une autre méthode

        ]);

    }
}
