<?php

namespace Modules\Absence\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Absence\Entities\TypeVacation;

class TypeVacationSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        $types = [
            ['code' => 'ANN', 'libelle' => 'Congés Annuels', 'require_certify' => '1'],
            ['code' => 'MAL', 'libelle' => 'Congés Maladie', 'require_certify' => '0'],
            ['code' => 'COM', 'libelle' => 'Congés de Compensation', 'require_certify' => '1'],
            ['code' => 'URG', 'libelle' => 'Congés d\'Urgences', 'require_certify' => '0'],
            ['code' => 'PAT', 'libelle' => 'Congés de Paternité', 'require_certify' => '0'],
            ['code' => 'MAT', 'libelle' => 'Congés de Maternité', 'require_certify' => '0'],
        ];

        foreach ($types as $typeData) {
            TypeVacation::updateOrCreate(
                [
                    'code' => $typeData['code'],
                ],
                $typeData
            );
        }

        $this->command->info('✅ Types de congés créés.');
    }
}
