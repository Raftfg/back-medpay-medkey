<?php

namespace Modules\Movment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Movment\Entities\Vaccination;
use Modules\Patient\Entities\Patiente;
use Illuminate\Support\Str;

class VaccinationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Vérifier que la table existe
        if (!\Illuminate\Support\Facades\Schema::hasTable('vaccinations')) {
            $this->command->warn("⚠️  La table 'vaccinations' n'existe pas. Veuillez d'abord exécuter les migrations.");
            return;
        }

        $patients = Patiente::limit(2)->get();
        
        if ($patients->isEmpty()) {
            $this->command->warn("⚠️  Aucun patient trouvé. Veuillez d'abord créer des patients.");
            return;
        }

        $vaccinations = [
            [
                'vaccine_name' => 'Vaccin contre la COVID-19 (Pfizer-BioNTech)',
                'vaccine_code' => 'COVID-19',
                'vaccination_date' => '2021-06-15',
                'batch_number' => 'EW0167',
                'administration_route' => 'IM',
                'site' => 'Bras gauche',
                'notes' => 'Première dose. Aucune réaction indésirable observée.',
                'next_dose_date' => '2021-07-06',
                'doctor_id' => auth()->id() ?? 1,
            ],
            [
                'vaccine_name' => 'Vaccin DTP (Diphtérie, Tétanos, Poliomyélite)',
                'vaccine_code' => 'DTP',
                'vaccination_date' => '2023-03-20',
                'batch_number' => 'DT2023-045',
                'administration_route' => 'IM',
                'site' => 'Bras droit',
                'notes' => 'Rappel décennal. Vaccination effectuée dans le cadre du suivi médical régulier.',
                'next_dose_date' => '2033-03-20',
                'doctor_id' => auth()->id() ?? 1,
            ],
        ];

        foreach ($patients as $index => $patient) {
            if (isset($vaccinations[$index])) {
                Vaccination::updateOrCreate(
                    [
                        'patients_id' => $patient->id,
                        'vaccine_name' => $vaccinations[$index]['vaccine_name'],
                        'vaccination_date' => $vaccinations[$index]['vaccination_date'],
                    ],
                    array_merge($vaccinations[$index], [
                        'uuid' => Str::uuid(),
                        'patients_id' => $patient->id,
                    ])
                );
            }
        }

        $this->command->info('✅ 2 vaccinations créées.');
    }
}
