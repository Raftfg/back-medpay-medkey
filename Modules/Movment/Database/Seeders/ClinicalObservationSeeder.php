<?php

namespace Modules\Movment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Movment\Entities\ClinicalObservation;
use Modules\Patient\Entities\Patiente;
use Illuminate\Support\Str;

class ClinicalObservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Vérifier que la table existe
        if (!\Illuminate\Support\Facades\Schema::hasTable('clinical_observations')) {
            $this->command->warn("⚠️  La table 'clinical_observations' n'existe pas. Veuillez d'abord exécuter les migrations.");
            return;
        }

        $patients = Patiente::limit(2)->get();
        
        if ($patients->isEmpty()) {
            $this->command->warn("⚠️  Aucun patient trouvé. Veuillez d'abord créer des patients.");
            return;
        }

        $observations = [
            [
                'subjective' => 'Le patient se plaint de douleurs thoraciques depuis 2 heures, associées à une dyspnée. Antécédents d\'hypertension artérielle.',
                'objective' => 'TA: 150/95 mmHg, FC: 98 bpm, Temp: 37.2°C, SpO2: 96%. Auscultation cardiaque normale. Pas de signes d\'insuffisance cardiaque.',
                'assessment' => 'Douleur thoracique d\'origine probablement cardiaque. Nécessité d\'un ECG et d\'un dosage des troponines pour éliminer un syndrome coronarien aigu.',
                'plan' => 'ECG immédiat, dosage des troponines, surveillance des constantes vitales. Traitement par aspirine 300mg et clopidogrel 300mg en attente des résultats.',
                'blood_pressure' => '150/95',
                'heart_rate' => 98,
                'temperature' => 37.2,
                'respiratory_rate' => 18,
                'oxygen_saturation' => 96,
                'weight' => 75.5,
                'height' => 175,
                'observation_date' => now()->subDays(2),
                'type' => 'consultation',
            ],
            [
                'subjective' => 'Consultation de suivi pour diabète de type 2. Le patient rapporte une bonne observance du traitement. Pas de symptômes particuliers.',
                'objective' => 'TA: 130/80 mmHg, FC: 72 bpm, Temp: 36.8°C, SpO2: 98%. Glycémie capillaire: 6.2 mmol/L. Examen général normal.',
                'assessment' => 'Diabète de type 2 bien équilibré. Glycémie dans les objectifs. Pas de complications décelées.',
                'plan' => 'Poursuite du traitement actuel. Contrôle glycémique dans 3 mois. Dosage de l\'HbA1c recommandé. Conseils diététiques renouvelés.',
                'blood_pressure' => '130/80',
                'heart_rate' => 72,
                'temperature' => 36.8,
                'respiratory_rate' => 16,
                'oxygen_saturation' => 98,
                'weight' => 75.5,
                'height' => 175,
                'observation_date' => now()->subDays(5),
                'type' => 'suivi',
            ],
        ];

        foreach ($patients as $index => $patient) {
            if (isset($observations[$index])) {
                ClinicalObservation::updateOrCreate(
                    [
                        'patients_id' => $patient->id,
                        'observation_date' => $observations[$index]['observation_date'],
                    ],
                    array_merge($observations[$index], [
                        'uuid' => Str::uuid(),
                        'patients_id' => $patient->id,
                        'doctor_id' => auth()->id() ?? 1,
                    ])
                );
            }
        }

        $this->command->info('✅ 2 observations cliniques créées.');
    }
}
