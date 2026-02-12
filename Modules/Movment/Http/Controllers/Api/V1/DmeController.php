<?php

namespace Modules\Movment\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Modules\Movment\Repositories\MovmentRepositoryEloquent;
use Modules\Movment\Repositories\AntecedentRepositoryEloquent;
use Modules\Movment\Repositories\AllergieRepositoryEloquent;
use Modules\Movment\Repositories\ClinicalObservationRepositoryEloquent;
use Modules\Movment\Repositories\VaccinationRepositoryEloquent;
use Modules\Movment\Entities\Antecedent;
use Modules\Movment\Entities\Allergie;
use Modules\Movment\Entities\Prescription;
use Modules\Movment\Entities\DmeDocument;
use Modules\Patient\Repositories\PatienteRepositoryEloquent;
use Modules\Medicalservices\Entities\ConsultationRecord;
use Modules\Medicalservices\Entities\LaboratoireRecord;
use Modules\Medicalservices\Entities\ImagerieRecord;

class DmeController extends Controller
{
    protected $movmentRepository, $antecedentRepository, $allergieRepository, 
              $clinicalObservationRepository, $vaccinationRepository, $patienteRepository;

    public function __construct(
        MovmentRepositoryEloquent $movmentRepository,
        AntecedentRepositoryEloquent $antecedentRepository,
        AllergieRepositoryEloquent $allergieRepository,
        ClinicalObservationRepositoryEloquent $clinicalObservationRepository,
        VaccinationRepositoryEloquent $vaccinationRepository,
        PatienteRepositoryEloquent $patienteRepository
    ) {
        $this->movmentRepository = $movmentRepository;
        $this->antecedentRepository = $antecedentRepository;
        $this->allergieRepository = $allergieRepository;
        $this->clinicalObservationRepository = $clinicalObservationRepository;
        $this->vaccinationRepository = $vaccinationRepository;
        $this->patienteRepository = $patienteRepository;
    }

    /**
     * Récupère le DME complet d'un patient (F2.1)
     */
    public function getFullDme($patientUuid)
    {
        try {
            $patient = $this->patienteRepository->findByUuid($patientUuid)->first();
            
            if (!$patient) {
                return reponse_json_transform([
                    'message' => 'Patient non trouvé',
                    'error' => 'Le patient avec cet UUID n\'existe pas.'
                ], 404);
            }

            // Récupérer toutes les données du DME avec gestion d'erreur pour chaque table
            $antecedents = $this->safeGetData(function() use ($patient) {
                if (Schema::hasTable('antecedents')) {
                    return Antecedent::where('patients_id', $patient->id)
                        ->orderBy('created_at', 'desc')
                        ->get();
                }
                return collect([]);
            });
            
            $allergies = $this->safeGetData(function() use ($patient) {
                if (Schema::hasTable('allergies')) {
                    return Allergie::where('patients_id', $patient->id)
                        ->orderBy('created_at', 'desc')
                        ->get();
                }
                return collect([]);
            });
            
            $observations = $this->safeGetData(function() use ($patient) {
                if (Schema::hasTable('clinical_observations')) {
                    return $this->clinicalObservationRepository
                        ->with(['doctor', 'movment'])
                        ->orderBy('observation_date', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->findWhere(['patients_id' => $patient->id]);
                }
                return collect([]);
            });
            
            $vaccinations = $this->safeGetData(function() use ($patient) {
                if (Schema::hasTable('vaccinations')) {
                    return $this->vaccinationRepository
                        ->with(['doctor', 'movment'])
                        ->orderBy('vaccination_date', 'desc')
                        ->findWhere(['patients_id' => $patient->id]);
                }
                return collect([]);
            });
            
            $movements = $this->safeGetData(function() use ($patient) {
                return $this->movmentRepository
                    ->orderBy('arrivaldate', 'desc')
                    ->findWhere(['patients_id' => $patient->id]);
            });

            // Récupérer les consultations via les mouvements
            $movementIds = $this->safeGetData(function() use ($patient) {
                return $this->movmentRepository->findWhere(['patients_id' => $patient->id])->pluck('id');
            }, collect([]));
            
            $consultations = $this->safeGetData(function() use ($movementIds) {
                if (Schema::hasTable('consultation_records') && $movementIds->isNotEmpty()) {
                    return ConsultationRecord::whereIn('movments_id', $movementIds)
                        ->orderBy('created_at', 'desc')
                        ->get();
                }
                return collect([]);
            });

            // Récupérer les examens de laboratoire
            $labExams = $this->safeGetData(function() use ($movementIds) {
                if (Schema::hasTable('laboratoire_records') && $movementIds->isNotEmpty()) {
                    return LaboratoireRecord::whereIn('movments_id', $movementIds)
                        ->orderBy('created_at', 'desc')
                        ->get();
                }
                return collect([]);
            });

            // Récupérer les examens d'imagerie
            $imagingExams = $this->safeGetData(function() use ($movementIds) {
                if (Schema::hasTable('imagerie_records') && $movementIds->isNotEmpty()) {
                    return ImagerieRecord::whereIn('movments_id', $movementIds)
                        ->orderBy('created_at', 'desc')
                        ->get();
                }
                return collect([]);
            });

            // Récupérer les prescriptions
            $prescriptions = $this->safeGetData(function() use ($patient) {
                if (Schema::hasTable('prescriptions')) {
                    return Prescription::with(['items', 'doctor'])
                        ->where('patients_id', $patient->id)
                        ->orderBy('prescription_date', 'desc')
                        ->get();
                }
                return collect([]);
            });

            // Récupérer les documents
            $documents = $this->safeGetData(function() use ($patient) {
                if (Schema::hasTable('dme_documents')) {
                    return DmeDocument::with(['uploadedBy'])
                        ->where('patients_id', $patient->id)
                        ->orderBy('document_date', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->get();
                }
                return collect([]);
            });

            return reponse_json_transform([
                'patient' => $patient,
                'antecedents' => $antecedents,
                'allergies' => $allergies,
                'observations' => $observations,
                'vaccinations' => $vaccinations,
                'movements' => $movements,
                'consultations' => $consultations,
                'lab_exams' => $labExams,
                'imaging_exams' => $imagingExams,
                'prescriptions' => $prescriptions,
                'documents' => $documents,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du DME: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'patient_uuid' => $patientUuid
            ]);
            return reponse_json_transform([
                'message' => 'Erreur lors de la récupération du dossier médical',
                'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue. Veuillez contacter l\'administrateur.'
            ], 500);
        }
    }

    /**
     * Exécute une fonction de manière sécurisée et retourne une valeur par défaut en cas d'erreur
     */
    private function safeGetData(callable $callback, $default = null)
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            Log::warning('Erreur lors de la récupération de données DME: ' . $e->getMessage());
            return $default ?? collect([]);
        }
    }

    /**
     * Génère un résumé intelligent via IA (F2.5)
     */
    public function getAiSummary($patientUuid)
    {
        try {
            $patient = $this->patienteRepository->findByUuid($patientUuid)->first();
            
            if (!$patient) {
                return reponse_json_transform([
                    'message' => 'Patient non trouvé'
                ], 404);
            }
            
            // Récupérer les données critiques avec gestion d'erreur
            $criticalAllergies = $this->safeGetData(function() use ($patient) {
                if (Schema::hasTable('allergies')) {
                    return $this->allergieRepository->findWhere([
                        'patients_id' => $patient->id,
                        'severity' => ['sévère', 'anaphylaxie']
                    ])->pluck('name');
                }
                return collect([]);
            }, collect([]));

            $majorAntecedents = $this->safeGetData(function() use ($patient) {
                if (Schema::hasTable('antecedents')) {
                    return $this->antecedentRepository
                        ->findWhere(['patients_id' => $patient->id, 'type' => 'médical'])
                        ->take(5)
                        ->pluck('name');
                }
                return collect([]);
            }, collect([]));

            $lastObs = $this->safeGetData(function() use ($patient) {
                if (Schema::hasTable('clinical_observations')) {
                    return $this->clinicalObservationRepository
                        ->orderBy('observation_date', 'desc')
                        ->findWhere(['patients_id' => $patient->id])
                        ->first();
                }
                return null;
            });

            $recentVaccinations = $this->safeGetData(function() use ($patient) {
                if (Schema::hasTable('vaccinations')) {
                    return $this->vaccinationRepository
                        ->orderBy('vaccination_date', 'desc')
                        ->findWhere(['patients_id' => $patient->id])
                        ->take(3);
                }
                return collect([]);
            }, collect([]));

            // Générer le résumé structuré
            $summary = "Résumé médical pour " . $patient->lastname . " " . $patient->firstname . " (IPP: " . $patient->ipp . "). ";
            
            if ($criticalAllergies->count() > 0) {
                $summary .= "⚠️ ALERTE ALLERGIES CRITIQUES: " . implode(', ', $criticalAllergies->toArray()) . ". ";
            }
            
            if ($majorAntecedents->count() > 0) {
                $summary .= "Antécédents médicaux principaux: " . implode(', ', $majorAntecedents->toArray()) . ". ";
            }
            
            if ($lastObs) {
                $summary .= "Dernière consultation: " . ($lastObs->observation_date ? $lastObs->observation_date->format('d/m/Y') : 'Date non renseignée') . ". ";
                if ($lastObs->assessment) {
                    $summary .= "Diagnostic: " . substr($lastObs->assessment, 0, 100) . ". ";
                }
            }
            
            if ($recentVaccinations->count() > 0) {
                $summary .= "Vaccinations récentes: " . $recentVaccinations->pluck('vaccine_name')->implode(', ') . ". ";
            }

            return reponse_json_transform([
                'summary' => $summary,
                'generated_at' => now(),
                'provider' => 'Medkey AI Engine',
                'critical_allergies_count' => $criticalAllergies->count(),
                'antecedents_count' => $majorAntecedents->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du résumé IA: ' . $e->getMessage());
            return reponse_json_transform([
                'summary' => 'Erreur lors de la génération du résumé. Veuillez réessayer.',
                'generated_at' => now(),
                'provider' => 'Medkey AI Engine'
            ]);
        }
    }

    /**
     * Recherche des codes CIM-10
     */
    public function searchCim10(Request $request)
    {
        $query = $request->input('q', '');
        
        // Base de données simplifiée de codes CIM-10 courants
        $cim10Codes = [
            ['code' => 'I10', 'label' => 'Hypertension essentielle (primitive)'],
            ['code' => 'E11', 'label' => 'Diabète sucré de type 2'],
            ['code' => 'E10', 'label' => 'Diabète sucré de type 1'],
            ['code' => 'J45', 'label' => 'Asthme'],
            ['code' => 'J44', 'label' => 'Autre maladie pulmonaire obstructive chronique'],
            ['code' => 'I20', 'label' => 'Angine de poitrine'],
            ['code' => 'I21', 'label' => 'Infarctus aigu du myocarde'],
            ['code' => 'I50', 'label' => 'Insuffisance cardiaque'],
            ['code' => 'K25', 'label' => 'Ulcère gastrique'],
            ['code' => 'K29', 'label' => 'Gastrite et duodénite'],
            ['code' => 'N18', 'label' => 'Insuffisance rénale chronique'],
            ['code' => 'M79', 'label' => 'Autres affections des tissus mous'],
            ['code' => 'G93', 'label' => 'Autres lésions cérébrales'],
            ['code' => 'A15', 'label' => 'Tuberculose respiratoire'],
            ['code' => 'B20', 'label' => 'Maladie à VIH'],
        ];

        if (empty($query)) {
            return response()->json($cim10Codes);
        }

        // Filtrer par code ou label
        $filtered = array_filter($cim10Codes, function($item) use ($query) {
            return stripos($item['code'], $query) !== false || 
                   stripos($item['label'], $query) !== false;
        });

        return response()->json(array_values($filtered));
    }
}
