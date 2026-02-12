<?php

namespace Modules\Movment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Movment\Entities\Antecedent;
use Modules\Patient\Entities\Patiente;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AntecedentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $patients = Patiente::limit(2)->get();
        
        if ($patients->isEmpty()) {
            $this->command->warn("⚠️  Aucun patient trouvé. Veuillez d'abord créer des patients.");
            return;
        }

        // Ajouter les colonnes manquantes si nécessaire
        $this->ensureColumnsExist();

        // Vérifier quelles colonnes existent dans la table (après ajout)
        $hasCim10Code = \Illuminate\Support\Facades\Schema::hasColumn('antecedents', 'cim10_code');
        $hasStartDate = \Illuminate\Support\Facades\Schema::hasColumn('antecedents', 'start_date');
        $hasEndDate = \Illuminate\Support\Facades\Schema::hasColumn('antecedents', 'end_date');
        $hasIsCured = \Illuminate\Support\Facades\Schema::hasColumn('antecedents', 'is_cured');

        $antecedents = [
            [
                'type' => 'médical',
                'name' => 'Hypertension artérielle',
                'description' => 'Hypertension artérielle essentielle diagnostiquée en 2020. Traitement en cours avec inhibiteurs de l\'enzyme de conversion.' . ($hasCim10Code ? ' (CIM-10: I10)' : ''),
            ],
            [
                'type' => 'chirurgical',
                'name' => 'Appendicectomie',
                'description' => 'Appendicectomie réalisée en 2018 suite à une appendicite aiguë. Intervention réussie sans complications.' . ($hasCim10Code ? ' (CIM-10: K35)' : ''),
            ],
        ];

        // Ajouter les colonnes optionnelles si elles existent
        if ($hasCim10Code) {
            $antecedents[0]['cim10_code'] = 'I10';
            $antecedents[1]['cim10_code'] = 'K35';
        }
        if ($hasStartDate) {
            $antecedents[0]['start_date'] = '2020-01-15';
            $antecedents[1]['start_date'] = '2018-06-20';
        }
        if ($hasEndDate) {
            $antecedents[0]['end_date'] = null;
            $antecedents[1]['end_date'] = '2018-07-15';
        }
        if ($hasIsCured) {
            $antecedents[0]['is_cured'] = false;
            $antecedents[1]['is_cured'] = true;
        }

        foreach ($patients as $index => $patient) {
            if (isset($antecedents[$index])) {
                Antecedent::updateOrCreate(
                    [
                        'patients_id' => $patient->id,
                        'name' => $antecedents[$index]['name'],
                    ],
                    array_merge($antecedents[$index], [
                        'uuid' => Str::uuid(),
                        'patients_id' => $patient->id,
                    ])
                );
            }
        }

        $this->command->info('✅ 2 antécédents créés.');
    }

    /**
     * S'assure que toutes les colonnes nécessaires existent dans la table antecedents
     */
    private function ensureColumnsExist()
    {
        try {
            \Illuminate\Support\Facades\Schema::table('antecedents', function ($table) {
                // Agrandir la colonne description si elle existe et est de type string
                if (\Illuminate\Support\Facades\Schema::hasColumn('antecedents', 'description')) {
                    try {
                        $table->text('description')->nullable()->change();
                    } catch (\Exception $e) {
                        // Si le changement échoue, utiliser SQL direct
                        \Illuminate\Support\Facades\DB::statement('ALTER TABLE antecedents MODIFY description TEXT NULL');
                    }
                }

                // Ajouter les colonnes manquantes
                if (!\Illuminate\Support\Facades\Schema::hasColumn('antecedents', 'cim10_code')) {
                    $table->string('cim10_code')->nullable()->after('type');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('antecedents', 'start_date')) {
                    $table->date('start_date')->nullable()->after('description');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('antecedents', 'end_date')) {
                    $table->date('end_date')->nullable()->after('start_date');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('antecedents', 'is_cured')) {
                    $table->boolean('is_cured')->default(false)->after('end_date');
                }
            });
        } catch (\Exception $e) {
            $this->command->warn("⚠️  Erreur lors de l'ajout des colonnes: " . $e->getMessage());
        }
    }
}
