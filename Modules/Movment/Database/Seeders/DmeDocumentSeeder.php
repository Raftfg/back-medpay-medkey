<?php

namespace Modules\Movment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Movment\Entities\DmeDocument;
use Modules\Patient\Entities\Patiente;
use Illuminate\Support\Str;

class DmeDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Vérifier que la table existe
        if (!\Illuminate\Support\Facades\Schema::hasTable('dme_documents')) {
            $this->command->warn("⚠️  La table 'dme_documents' n'existe pas. Veuillez d'abord exécuter les migrations.");
            return;
        }

        $patients = Patiente::limit(2)->get();
        
        if ($patients->isEmpty()) {
            $this->command->warn("⚠️  Aucun patient trouvé. Veuillez d'abord créer des patients.");
            return;
        }

        $documents = [
            [
                'title' => 'Radiographie thorax',
                'type' => 'imagerie',
                'file_path' => '/storage/documents/radiographie_thorax_001.pdf',
                'file_name' => 'radiographie_thorax_001.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 245678,
                'description' => 'Radiographie thorax de face et de profil réalisée dans le cadre du bilan de douleur thoracique. Pas d\'anomalie décelée.',
                'document_date' => now()->subDays(2),
                'uploaded_by' => auth()->id() ?? 1,
            ],
            [
                'title' => 'Compte-rendu d\'hospitalisation',
                'type' => 'compte_rendu',
                'file_path' => '/storage/documents/cr_hospitalisation_001.pdf',
                'file_name' => 'cr_hospitalisation_001.pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 456789,
                'description' => 'Compte-rendu d\'hospitalisation suite à l\'admission pour bilan cardiaque. Durée de séjour: 3 jours. Sortie avec traitement.',
                'document_date' => now()->subDays(5),
                'uploaded_by' => auth()->id() ?? 1,
            ],
        ];

        foreach ($patients as $index => $patient) {
            if (isset($documents[$index])) {
                DmeDocument::updateOrCreate(
                    [
                        'patients_id' => $patient->id,
                        'title' => $documents[$index]['title'],
                        'document_date' => $documents[$index]['document_date'],
                    ],
                    array_merge($documents[$index], [
                        'uuid' => Str::uuid(),
                        'patients_id' => $patient->id,
                    ])
                );
            }
        }

        $this->command->info('✅ 2 documents DME créés.');
    }
}
