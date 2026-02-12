<?php

namespace Modules\Movment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Movment\Entities\Prescription;
use Modules\Movment\Entities\PrescriptionItem;
use Modules\Patient\Entities\Patiente;
use Illuminate\Support\Str;

class PrescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Vérifier que les tables existent
        if (!\Illuminate\Support\Facades\Schema::hasTable('prescriptions')) {
            $this->command->warn("⚠️  La table 'prescriptions' n'existe pas. Veuillez d'abord exécuter les migrations.");
            return;
        }
        if (!\Illuminate\Support\Facades\Schema::hasTable('prescription_items')) {
            $this->command->warn("⚠️  La table 'prescription_items' n'existe pas. Veuillez d'abord exécuter les migrations.");
            return;
        }

        $patients = Patiente::limit(2)->get();
        
        if ($patients->isEmpty()) {
            $this->command->warn("⚠️  Aucun patient trouvé. Veuillez d'abord créer des patients.");
            return;
        }

        $prescriptions = [
            [
                'prescription_date' => now()->subDays(3),
                'status' => 'active',
                'valid_until' => now()->addDays(7),
                'notes' => 'Traitement antibiotique pour infection respiratoire. Respecter les horaires de prise.',
                'items' => [
                    [
                        'medication_name' => 'Amoxicilline',
                        'dosage' => '1000mg',
                        'form' => 'Comprimé',
                        'administration_route' => 'Orale',
                        'quantity' => 21,
                        'frequency' => '3 fois par jour',
                        'instructions' => 'Avant les repas',
                        'duration_days' => 7,
                    ],
                    [
                        'medication_name' => 'Paracétamol',
                        'dosage' => '500mg',
                        'form' => 'Comprimé',
                        'administration_route' => 'Orale',
                        'quantity' => 20,
                        'frequency' => '3 fois par jour si douleur ou fièvre',
                        'instructions' => 'Espacer les prises d\'au moins 4 heures',
                        'duration_days' => 5,
                    ],
                ],
            ],
            [
                'prescription_date' => now()->subDays(10),
                'status' => 'completed',
                'valid_until' => now()->subDays(3),
                'notes' => 'Traitement antihypertenseur. Contrôle de la tension artérielle recommandé après 2 semaines.',
                'items' => [
                    [
                        'medication_name' => 'Lisinopril',
                        'dosage' => '10mg',
                        'form' => 'Comprimé',
                        'administration_route' => 'Orale',
                        'quantity' => 30,
                        'frequency' => '1 fois par jour',
                        'instructions' => 'Le matin, de préférence à jeun',
                        'duration_days' => 30,
                    ],
                ],
            ],
        ];

        foreach ($patients as $index => $patient) {
            if (isset($prescriptions[$index])) {
                $prescriptionData = $prescriptions[$index];
                $items = $prescriptionData['items'];
                unset($prescriptionData['items']);

                $prescription = Prescription::updateOrCreate(
                    [
                        'patients_id' => $patient->id,
                        'prescription_date' => $prescriptionData['prescription_date'],
                    ],
                    array_merge($prescriptionData, [
                        'uuid' => Str::uuid(),
                        'patients_id' => $patient->id,
                        'doctor_id' => auth()->id() ?? 1,
                    ])
                );

                // Créer les items de prescription
                foreach ($items as $item) {
                    PrescriptionItem::updateOrCreate(
                        [
                            'prescription_id' => $prescription->id,
                            'medication_name' => $item['medication_name'],
                        ],
                        array_merge($item, [
                            'prescription_id' => $prescription->id,
                            'status' => 'pending',
                        ])
                    );
                }
            }
        }

        $this->command->info('✅ 2 prescriptions créées avec leurs items.');
    }
}
