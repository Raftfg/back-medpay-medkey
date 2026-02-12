<?php

namespace Modules\Movment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Movment\Entities\Allergie;
use Modules\Patient\Entities\Patiente;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AllergieSeeder extends Seeder
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
        $hasSeverity = \Illuminate\Support\Facades\Schema::hasColumn('allergies', 'severity');
        $hasDiscoveryDate = \Illuminate\Support\Facades\Schema::hasColumn('allergies', 'discovery_date');
        $hasReactions = \Illuminate\Support\Facades\Schema::hasColumn('allergies', 'reactions');

        $allergies = [
            [
                'name' => 'Pénicilline',
                'type' => 'médicament',
                'description' => 'Allergie confirmée par test cutané. Contre-indication absolue à tous les dérivés de la pénicilline. ' . ($hasReactions ? 'Réactions: Urticaire généralisée, œdème de Quincke, difficultés respiratoires.' : ''),
            ],
            [
                'name' => 'Arachides',
                'type' => 'aliment',
                'description' => 'Allergie sévère aux arachides et produits dérivés. Le patient doit toujours avoir un stylo d\'adrénaline sur lui. ' . ($hasReactions ? 'Réactions: Choc anaphylactique avec perte de conscience.' : ''),
            ],
        ];

        // Ajouter les colonnes optionnelles si elles existent
        if ($hasSeverity) {
            $allergies[0]['severity'] = 'sévère';
            $allergies[1]['severity'] = 'anaphylaxie';
        }
        if ($hasDiscoveryDate) {
            $allergies[0]['discovery_date'] = '2019-03-10';
            $allergies[1]['discovery_date'] = '2015-08-22';
        }
        if ($hasReactions) {
            $allergies[0]['reactions'] = 'Urticaire généralisée, œdème de Quincke, difficultés respiratoires. Réaction observée lors d\'un traitement antibiotique.';
            $allergies[1]['reactions'] = 'Choc anaphylactique avec perte de conscience, nécessitant une injection d\'adrénaline en urgence.';
        }

        foreach ($patients as $index => $patient) {
            if (isset($allergies[$index])) {
                Allergie::updateOrCreate(
                    [
                        'patients_id' => $patient->id,
                        'name' => $allergies[$index]['name'],
                    ],
                    array_merge($allergies[$index], [
                        'uuid' => Str::uuid(),
                        'patients_id' => $patient->id,
                    ])
                );
            }
        }

        $this->command->info('✅ 2 allergies créées.');
    }

    /**
     * S'assure que toutes les colonnes nécessaires existent dans la table allergies
     */
    private function ensureColumnsExist()
    {
        try {
            \Illuminate\Support\Facades\Schema::table('allergies', function ($table) {
                // Agrandir la colonne description si elle existe et est de type string
                if (\Illuminate\Support\Facades\Schema::hasColumn('allergies', 'description')) {
                    try {
                        $table->text('description')->nullable()->change();
                    } catch (\Exception $e) {
                        // Si le changement échoue, utiliser SQL direct
                        \Illuminate\Support\Facades\DB::statement('ALTER TABLE allergies MODIFY description TEXT NULL');
                    }
                }

                // Ajouter les colonnes manquantes
                if (!\Illuminate\Support\Facades\Schema::hasColumn('allergies', 'severity')) {
                    $table->string('severity')->nullable()->after('type');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('allergies', 'reactions')) {
                    $table->text('reactions')->nullable()->after('severity');
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('allergies', 'discovery_date')) {
                    $table->date('discovery_date')->nullable()->after('reactions');
                }
            });
        } catch (\Exception $e) {
            $this->command->warn("⚠️  Erreur lors de l'ajout des colonnes: " . $e->getMessage());
        }
    }
}
