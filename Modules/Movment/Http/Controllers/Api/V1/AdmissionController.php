<?php

namespace Modules\Movment\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Modules\Movment\Repositories\MovmentRepositoryEloquent;
use Modules\Hospitalization\Repositories\BedRepositoryEloquent;
use Modules\Hospitalization\Repositories\BedPatientRepositoryEloquent;
use Modules\Hospitalization\Entities\Bed;
use Modules\Patient\Repositories\PatienteRepositoryEloquent;
use Modules\Acl\Entities\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AdmissionController extends Controller
{
    protected $movmentRepository, $bedRepository, $bedPatientRepository, $patienteRepository;

    public function __construct(
        MovmentRepositoryEloquent $movmentRepository,
        BedRepositoryEloquent $bedRepository,
        BedPatientRepositoryEloquent $bedPatientRepository,
        PatienteRepositoryEloquent $patienteRepository
    ) {
        $this->movmentRepository = $movmentRepository;
        $this->bedRepository = $bedRepository;
        $this->bedPatientRepository = $bedPatientRepository;
        $this->patienteRepository = $patienteRepository;
    }

    /**
     * Effectue une admission complète (Mouvement + Lit)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function admit(Request $request)
    {
        // Validation de base
        $request->validate([
            'patient_id' => 'required|integer',
            'service_id' => 'required|integer',
            'admission_type' => 'required|in:programmée,urgence',
            'responsible_doctor_id' => 'nullable|integer',
            'bed_id' => 'nullable|integer',
            'incoming_reason' => 'nullable|string',
        ]);

        try {
            return DB::connection('tenant')->transaction(function () use ($request) {
                // Vérifier que les colonnes nécessaires existent
                if (!Schema::connection('tenant')->hasColumn('movments', 'admission_type')) {
                    Log::error('La colonne admission_type n\'existe pas dans la table movments');
                    return reponse_json_transform([
                        'message' => __("Erreur de configuration: la colonne admission_type est manquante."),
                        'error' => 'La migration pour ajouter admission_type n\'a pas été exécutée. Veuillez exécuter: php artisan tenant:migrate-all'
                    ], 500);
                }
                
                if (!Schema::connection('tenant')->hasColumn('movments', 'responsible_doctor_id')) {
                    Log::error('La colonne responsible_doctor_id n\'existe pas dans la table movments');
                    return reponse_json_transform([
                        'message' => __("Erreur de configuration: la colonne responsible_doctor_id est manquante."),
                        'error' => 'La migration pour ajouter responsible_doctor_id n\'a pas été exécutée. Veuillez exécuter: php artisan tenant:migrate-all'
                    ], 500);
                }
                
                // Vérifications dans la base tenant
                $patient = $this->patienteRepository->find($request->patient_id);
                if (!$patient) {
                    return reponse_json_transform([
                        'message' => __("Patient non trouvé."),
                    ], 404);
                }

                $service = DB::connection('tenant')->table('services')->where('id', $request->service_id)->first();
                if (!$service) {
                    return reponse_json_transform([
                        'message' => __("Service non trouvé."),
                    ], 404);
                }

                // Vérifier le médecin responsable si fourni
                $doctorId = null;
                if ($request->responsible_doctor_id) {
                    $doctor = User::on('tenant')->find($request->responsible_doctor_id);
                    if (!$doctor) {
                        return reponse_json_transform([
                            'message' => __("Médecin non trouvé."),
                            'error' => __("L'utilisateur avec cet ID n'existe pas dans ce tenant.")
                        ], 404);
                    }
                    $doctorId = $doctor->id;
                }

                // 1. Vérifier si un mouvement est déjà en cours
                $existing = $this->movmentRepository->findWhere([
                    'patients_id' => $request->patient_id,
                    'releasedate' => null
                ])->first();

                if ($existing) {
                    return reponse_json_transform([
                        'message' => __("Le patient a déjà un mouvement en cours."),
                        'data' => $existing
                    ], 422);
                }

                // 2. Créer le mouvement ADT
                try {
                    $iep = $this->generateIEP();
                } catch (\Exception $iepError) {
                    Log::error('Erreur génération IEP: ' . $iepError->getMessage());
                    // Valeur par défaut si la génération échoue
                    $iep = DB::connection('tenant')->table('movments')->max('iep') ?? 0;
                    $iep = (int)$iep + 1;
                }

                $movmentData = [
                    'uuid' => (string) Str::uuid(),
                    'patients_id' => $request->patient_id,
                    'active_services_id' => $request->service_id,
                    'active_services_code' => $service->code ?? $service->name ?? 'SERV-' . $request->service_id,
                    'admission_type' => $request->admission_type,
                    'arrivaldate' => Carbon::now(),
                    'incoming_reason' => $request->incoming_reason ?? null,
                    'ipp' => $patient->ipp ?? null,
                    'iep' => $iep,
                ];
                
                // Ajouter le médecin responsable seulement s'il est fourni et valide
                if ($doctorId) {
                    $movmentData['responsible_doctor_id'] = $doctorId;
                }
                
                Log::info('Création mouvement avec données:', $movmentData);
                
                try {
                    $movment = $this->movmentRepository->create($movmentData);
                } catch (\Exception $createError) {
                    Log::error('Erreur création mouvement: ' . $createError->getMessage(), [
                        'trace' => $createError->getTraceAsString(),
                        'data' => $movmentData
                    ]);
                    throw $createError;
                }

                // 3. Affecter le lit si spécifié ou trouver un lit disponible automatiquement
                $bedAssigned = false;
                
                if ($request->bed_id) {
                    $bed = $this->bedRepository->find($request->bed_id);
                    
                    if (!$bed) {
                        return reponse_json_transform([
                            'message' => __("Lit non trouvé."),
                        ], 404);
                    }
                    
                    if ($bed->state === 'busy') {
                        return reponse_json_transform([
                            'message' => __("Le lit sélectionné est déjà occupé."),
                        ], 422);
                    }

                    // Créer l'entrée dans l'historique d'occupation
                    $this->bedPatientRepository->create([
                        'uuid' => (string) Str::uuid(),
                        'bed_id' => $bed->id,
                        'patient_id' => $request->patient_id,
                        'movment_id' => $movment->id,
                        'start_occupation_date' => Carbon::now(),
                        'state' => 'busy',
                        'user_id' => auth()->id() ?? 1
                    ]);

                    // Mettre à jour l'état du lit
                    $this->bedRepository->update([
                        'state' => 'busy',
                        'patient_id' => $request->patient_id
                    ], $bed->id);
                    
                    $bedAssigned = true;
                } else {
                    // Auto-assignment : chercher un lit disponible dans le service
                    Log::info('Recherche automatique d\'un lit disponible', [
                        'service_id' => $request->service_id,
                        'movment_id' => $movment->id
                    ]);
                    
                    try {
                        // Utiliser le modèle directement pour éviter les limitations du repository Prettus avec whereHas
                        $availableBed = Bed::where('state', 'free')
                            ->whereHas('room', function($query) use ($request) {
                                $query->where('services_id', $request->service_id);
                            })
                            ->first();
                        
                        Log::info('Résultat de la recherche de lit disponible', [
                            'bed_found' => $availableBed ? true : false,
                            'bed_id' => $availableBed ? $availableBed->id : null,
                            'bed_code' => $availableBed ? $availableBed->code : null,
                            'bed_room_id' => $availableBed && $availableBed->room ? $availableBed->room->id : null
                        ]);
                        
                        if ($availableBed) {
                            Log::info('Affectation automatique du lit', [
                                'bed_id' => $availableBed->id,
                                'bed_code' => $availableBed->code,
                                'patient_id' => $request->patient_id,
                                'movment_id' => $movment->id
                            ]);
                            
                            // Créer l'entrée dans l'historique d'occupation
                            $bedPatient = $this->bedPatientRepository->create([
                                'uuid' => (string) Str::uuid(),
                                'bed_id' => $availableBed->id,
                                'patient_id' => $request->patient_id,
                                'movment_id' => $movment->id,
                                'start_occupation_date' => Carbon::now(),
                                'state' => 'busy',
                                'user_id' => auth()->id() ?? 1
                            ]);
                            
                            Log::info('Entrée bed_patients créée', [
                                'bed_patient_id' => $bedPatient->id,
                                'bed_patient_uuid' => $bedPatient->uuid
                            ]);

                            // Mettre à jour l'état du lit
                            $this->bedRepository->update([
                                'state' => 'busy',
                                'patient_id' => $request->patient_id
                            ], $availableBed->id);
                            
                            Log::info('État du lit mis à jour', [
                                'bed_id' => $availableBed->id,
                                'new_state' => 'busy',
                                'patient_id' => $request->patient_id
                            ]);
                            
                            $bedAssigned = true;
                        } else {
                            Log::warning('Aucun lit disponible trouvé pour le service', [
                                'service_id' => $request->service_id,
                                'movment_id' => $movment->id
                            ]);
                            
                            // Vérifier s'il y a des lits dans le service mais tous occupés
                            $totalBedsInService = Bed::whereHas('room', function($query) use ($request) {
                                    $query->where('services_id', $request->service_id);
                                })
                                ->count();
                            
                            $freeBedsInService = Bed::where('state', 'free')
                                ->whereHas('room', function($query) use ($request) {
                                    $query->where('services_id', $request->service_id);
                                })
                                ->count();
                            
                            Log::info('Statistiques des lits du service', [
                                'service_id' => $request->service_id,
                                'total_beds' => $totalBedsInService,
                                'free_beds' => $freeBedsInService,
                                'busy_beds' => $totalBedsInService - $freeBedsInService
                            ]);
                        }
                    } catch (\Exception $bedError) {
                        Log::error('Erreur lors de la recherche/affectation automatique de lit', [
                            'error' => $bedError->getMessage(),
                            'trace' => $bedError->getTraceAsString(),
                            'service_id' => $request->service_id
                        ]);
                        // Ne pas bloquer l'admission si l'affectation de lit échoue
                        // Le patient peut être admis sans lit (liste d'attente)
                    }
                }
                
                // Si aucun lit n'est disponible, on peut créer une entrée en liste d'attente
                // (Cette fonctionnalité peut être étendue avec une table waiting_list si nécessaire)

                $movment = $movment->fresh(['patient', 'bedPatients.bed', 'service']);
                
                return reponse_json_transform([
                    'message' => $bedAssigned 
                        ? __("Admission réussie avec affectation de lit")
                        : __("Admission réussie sans lit (liste d'attente)"),
                    'data' => $movment,
                    'bed_assigned' => $bedAssigned
                ], 201);
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return reponse_json_transform([
                'message' => __("Erreur de validation."),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'admission: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request' => $request->all()
            ]);
            
            return reponse_json_transform([
                'message' => __("Erreur lors de l'admission."),
                'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue',
                'debug' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null
            ], 500);
        }
    }

    /**
     * Transfert d'un patient entre services/lits (F1.3)
     */
    public function transfer(Request $request)
    {
        // Validation de base
        $request->validate([
            'movment_uuid' => 'required|string',
            'new_service_id' => 'required|integer',
            'new_bed_id' => 'nullable|integer',
            'transfer_reason' => 'required|string',
            'responsible_doctor_id' => 'nullable|integer',
        ]);

        try {
            return DB::connection('tenant')->transaction(function () use ($request) {
                $movment = $this->movmentRepository->findByUuid($request->movment_uuid)->first();
                
                if (!$movment) {
                    return reponse_json_transform([
                        'message' => __("Mouvement non trouvé."),
                    ], 404);
                }
                
                // Vérifier le service de destination
                $service = DB::connection('tenant')->table('services')->where('id', $request->new_service_id)->first();
                if (!$service) {
                    return reponse_json_transform([
                        'message' => __("Service de destination non trouvé."),
                    ], 404);
                }
                
                // Vérifier que le service de destination est différent du service actuel
                if ($movment->active_services_id == $request->new_service_id) {
                    return reponse_json_transform([
                        'message' => __("Le service de destination doit être différent du service actuel."),
                    ], 422);
                }

                // Vérifier le médecin responsable si fourni
                $doctorId = null;
                if ($request->responsible_doctor_id) {
                    $doctor = User::on('tenant')->find($request->responsible_doctor_id);
                    if (!$doctor) {
                        return reponse_json_transform([
                            'message' => __("Médecin non trouvé."),
                            'error' => __("L'utilisateur avec cet ID n'existe pas dans ce tenant.")
                        ], 404);
                    }
                    $doctorId = $doctor->id;
                }

                // 1. Libérer l'ancien lit si existant
                $currentBedPatient = $this->bedPatientRepository->findWhere([
                    'movment_id' => $movment->id,
                    'end_occupation_date' => null
                ])->first();

                if ($currentBedPatient) {
                    $this->bedPatientRepository->update([
                        'end_occupation_date' => Carbon::now(),
                        'state' => 'free'
                    ], $currentBedPatient->id);

                    $this->bedRepository->update([
                        'state' => 'free',
                        'patient_id' => null
                    ], $currentBedPatient->bed_id);
                }

                // 2. Affecter le nouveau lit si spécifié
                if ($request->new_bed_id) {
                    $newBed = $this->bedRepository->find($request->new_bed_id);
                    if (!$newBed) {
                        return reponse_json_transform([
                            'message' => __("Lit non trouvé."),
                        ], 404);
                    }
                    if ($newBed->state === 'busy') {
                        return reponse_json_transform([
                            'message' => __("Le nouveau lit est déjà occupé."),
                        ], 422);
                    }

                    $this->bedPatientRepository->create([
                        'uuid' => (string) Str::uuid(),
                        'bed_id' => $newBed->id,
                        'patient_id' => $movment->patients_id,
                        'movment_id' => $movment->id,
                        'start_occupation_date' => Carbon::now(),
                        'state' => 'busy',
                        'user_id' => auth()->id() ?? 1
                    ]);

                    $this->bedRepository->update([
                        'state' => 'busy',
                        'patient_id' => $movment->patients_id
                    ], $newBed->id);
                }

                // 3. Mettre à jour le mouvement
                $updateData = [
                    'active_services_id' => $request->new_service_id,
                ];
                
                // Mettre à jour le médecin responsable si fourni et valide
                if ($doctorId) {
                    $updateData['responsible_doctor_id'] = $doctorId;
                }
                
                $this->movmentRepository->update($updateData, $movment->id);
                
                $movment = $movment->fresh(['service', 'bedPatients.bed', 'patient']);

                return reponse_json_transform([
                    'message' => __("Transfert effectué avec succès"),
                    'data' => $movment
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Erreur lors du transfert: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return reponse_json_transform([
                'message' => __("Erreur lors du transfert."),
                'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue'
            ], 500);
        }
    }

    /**
     * Sortie du patient (F1.4)
     */
    public function release(Request $request)
    {
        $request->validate([
            'movment_uuid' => 'required|exists:tenant.movments,uuid',
            'outgoing_reason' => 'required|in:guérison,transfert,décès,contre avis médical',
            'destination' => 'nullable|string',
        ]);

        try {
            return DB::connection('tenant')->transaction(function () use ($request) {
                $movment = $this->movmentRepository->findByUuidOrFail($request->movment_uuid)->first();

                // 1. Libérer le lit actuel
                $currentBedPatient = $this->bedPatientRepository->findWhere([
                    'movment_id' => $movment->id,
                    'end_occupation_date' => null
                ])->first();

                if ($currentBedPatient) {
                    $this->bedPatientRepository->update([
                        'end_occupation_date' => Carbon::now(),
                        'state' => 'free'
                    ], $currentBedPatient->id);

                    $this->bedRepository->update([
                        'state' => 'free',
                        'patient_id' => null
                    ], $currentBedPatient->bed_id);
                }

                // 2. Vérifier que la date de sortie n'est pas antérieure à la date d'admission
                $arrivalDate = Carbon::parse($movment->arrivaldate);
                $releaseDate = Carbon::now();
                
                if ($releaseDate->lt($arrivalDate)) {
                    throw new \Exception(__("La date de sortie ne peut pas être antérieure à la date d'admission."));
                }
                
                // 3. Clôturer le mouvement
                $this->movmentRepository->update([
                    'releasedate' => $releaseDate,
                    'outgoing_reason' => $request->outgoing_reason,
                    'destination' => $request->destination ?? null,
                ], $movment->id);
                
                $movment = $movment->fresh(['patient', 'service']);

                // 4. Générer les documents de sortie (structure pour extension future)
                $documents = [
                    'certificat_sortie' => [
                        'type' => 'certificat_sortie',
                        'patient_name' => $movment->patient->lastname . ' ' . $movment->patient->firstname,
                        'ipp' => $movment->ipp,
                        'arrival_date' => $arrivalDate->format('d/m/Y H:i'),
                        'release_date' => $releaseDate->format('d/m/Y H:i'),
                        'outgoing_reason' => $request->outgoing_reason,
                        'destination' => $request->destination,
                    ],
                    'resume_sejour' => [
                        'type' => 'resume_sejour',
                        'patient_name' => $movment->patient->lastname . ' ' . $movment->patient->firstname,
                        'ipp' => $movment->ipp,
                        'iep' => $movment->iep,
                        'service' => $movment->service ? $movment->service->name : 'N/A',
                        'arrival_date' => $arrivalDate->format('d/m/Y H:i'),
                        'release_date' => $releaseDate->format('d/m/Y H:i'),
                        'duration_days' => $arrivalDate->diffInDays($releaseDate),
                    ]
                ];
                
                // Si décès, ajouter des documents spécifiques
                if ($request->outgoing_reason === 'décès') {
                    $documents['certificat_deces'] = [
                        'type' => 'certificat_deces',
                        'patient_name' => $movment->patient->lastname . ' ' . $movment->patient->firstname,
                        'date_deces' => $releaseDate->format('d/m/Y H:i'),
                    ];
                }

                return reponse_json_transform([
                    'message' => __("Sortie effectuée avec succès"),
                    'data' => $movment,
                    'documents' => $documents
                ]);
            });
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Génère un IEP suivant
     */
    private function generateIEP()
    {
        try {
            $last = $this->movmentRepository->orderBy('iep', 'desc')->first();
            if ($last && isset($last->iep) && is_numeric($last->iep)) {
                return (int)$last->iep + 1;
            }
            return 1;
        } catch (\Exception $e) {
            Log::warning('Erreur lors de la génération IEP, utilisation de la méthode alternative: ' . $e->getMessage());
            // Méthode alternative : utiliser DB directement
            $lastIep = DB::connection('tenant')->table('movments')->max('iep');
            return $lastIep ? (int)$lastIep + 1 : 1;
        }
    }
}
