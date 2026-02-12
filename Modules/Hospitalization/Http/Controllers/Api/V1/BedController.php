<?php

namespace Modules\Hospitalization\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Hospitalization\Entities\Bed;
use Modules\Hospitalization\Entities\Room;
use Modules\Acl\Repositories\UserRepository;
use Modules\Hospitalization\Http\Resources\BedResource;
use Modules\Hospitalization\Http\Resources\BedsResource;
use Modules\Hospitalization\Http\Requests\BedIndexRequest;
use Modules\Hospitalization\Http\Requests\BedStoreRequest;
use Modules\Hospitalization\Http\Requests\BedDeleteRequest;
use Modules\Hospitalization\Http\Requests\BedUpdateRequest;
use Modules\Patient\Repositories\PatienteRepositoryEloquent;
use Modules\Hospitalization\Repositories\BedRepositoryEloquent;
use Modules\Hospitalization\Repositories\RoomRepositoryEloquent;
use Modules\Hospitalization\Http\Controllers\HospitalizationController;

class BedController extends HospitalizationController {

    /**
     * @var BedRepositoryEloquent
     */
    protected $bedRepositoryEloquent;

    /**
     * @var RoomRepositoryEloquent
     */
    protected $roomRepositoryEloquent;

    /**
     * @var PatienteRepositoryEloquent
     */
    protected $patienteRepositoryEloquent;

    /**
     * @var UserRepository
     */
    protected $userRepositoryEloquent;

    public function __construct(
        BedRepositoryEloquent $bedRepositoryEloquent,
        RoomRepositoryEloquent $roomRepositoryEloquent,
        PatienteRepositoryEloquent $patienteRepositoryEloquent,
        UserRepository $userRepositoryEloquent,
    ) {
        parent::__construct();
        $this->bedRepositoryEloquent = $bedRepositoryEloquent;
        $this->roomRepositoryEloquent = $roomRepositoryEloquent;
        $this->patienteRepositoryEloquent = $patienteRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }

    
    /**
     * Return a listing of the resource.
     * @param BedIndexRequest $request
     * @return BedsResource
     */
    public function index(BedIndexRequest $request)
    {
        try {
            // Charger les relations nécessaires pour éviter les erreurs null
            // Trier par date de création décroissante pour afficher les plus récents en premier
            // Utiliser le modèle directement pour éviter les conflits avec RequestCriteria du repository
            $data = Bed::query()
                ->with(['room', 'patient', 'currentStay.patient'])
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc') // Tri secondaire par ID pour garantir l'ordre
                ->paginate($this->nombrePage);
            
            // DEBUG: Logger le nombre de lits trouvés
            Log::info("DEBUG - Nombre de lits trouvés", [
                'total' => $data->total(),
                'count' => $data->count(),
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
            ]);
            
            $resource = new BedsResource($data);
            $response = $resource->toArray($request);
            
            // DEBUG: Logger la structure de la réponse
            Log::info("DEBUG - Structure de la réponse BedsResource", [
                'has_data' => isset($response['data']),
                'data_is_array' => isset($response['data']) && is_array($response['data']),
                'data_count' => isset($response['data']) && is_array($response['data']) ? count($response['data']) : 0,
            ]);
            
            return $resource;
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération des lits: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return reponse_json_transform([
                'message' => 'Une erreur est survenue lors de la récupération des lits. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     * @param BedIndexRequest $request
     * @param string $uuid
     * @return BedResource
     */ 
    public function show(BedIndexRequest $request, $uuid) 
    {
        try {
            $item = $this->bedRepositoryEloquent->findByUuid($uuid)->first();

            if (!$item) {
                return reponse_json_transform(['message' => 'Lit non trouvé'], 404);
            }

            return new BedResource($item);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la récupération d'un lit: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'uuid' => $uuid
            ]);
            return reponse_json_transform([
                'message' => 'Une erreur est survenue lors de la récupération du lit. Veuillez réessayer.'
            ], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param BedStoreRequest $request
     * @return BedResource
     */
    public function store(BedStoreRequest $request)
    {
        try {
            $attributes = $request->all();
            
            // Nettoyer les attributs pour ne garder que les champs autorisés
            $allowedAttributes = ['name', 'room_id'];
            $attributes = array_intersect_key($attributes, array_flip($allowedAttributes));
            
            $room = $this->roomRepositoryEloquent->findByUuid($attributes['room_id'] )->first();

            if (!$room) {
                return reponse_json_transform(['message' => 'Salle non trouvée'], 404);
            }

            // Charger la relation beds si elle n'est pas déjà chargée
            // Compter uniquement les lits non supprimés (soft delete)
            if (!$room->relationLoaded('beds')) {
                $room->load(['beds' => function ($query) {
                    $query->whereNull('deleted_at');
                }]);
            } else {
                // Si déjà chargée, filtrer les lits supprimés
                $room->setRelation('beds', $room->beds->whereNull('deleted_at'));
            }

            // Limite absolue de 10 lits par salle (prioritaire sur la capacité définie)
            $maxCapacity = 10;
            
            // Charger et compter les lits actuels
            $currentBedCount = $room->beds->count();
            
            // Vérifier d'abord la limite absolue de 10 lits (prioritaire)
            if ($currentBedCount >= $maxCapacity) {
                $message = sprintf(
                    'Impossible d\'ajouter un nouveau lit. La limite maximale de %d lits par salle est atteinte. Actuellement : %d lit(s) dans la salle "%s".',
                    $maxCapacity,
                    $currentBedCount,
                    $room->name ?? 'cette salle'
                );
                return reponse_json_transform(['message' => $message], 400);
            }

            // Vérifier que le nombre total de lits ne dépassera pas 10 après ajout
            if (($currentBedCount + 1) > $maxCapacity) {
                $message = sprintf(
                    'Impossible d\'ajouter un nouveau lit. La limite maximale de %d lits par salle serait dépassée. Actuellement : %d lit(s) dans la salle "%s".',
                    $maxCapacity,
                    $currentBedCount,
                    $room->name ?? 'cette salle'
                );
                return reponse_json_transform(['message' => $message], 400);
            }

            $item = DB::transaction(function () use ($attributes) {
                // Recharger la salle avec la relation beds pour avoir le comptage à jour
                // Exclure les lits soft-deleted du comptage
                $room = $this->roomRepositoryEloquent
                    ->with(['beds' => function ($query) {
                        $query->whereNull('deleted_at');
                    }])
                    ->findByUuid($attributes['room_id'])
                    ->first();

                // Nettoyer les attributs : retirer hospital_id (multi-tenant, une base par hôpital)
                unset($attributes['hospital_id']);
                
                // Nettoyer les attributs : retirer hospital_id (multi-tenant, une base par hôpital)
                // Le wrapper se charge du basculement entre les bases de données
                unset($attributes['hospital_id']);
                
                $attributes['user_id'] = auth()->user()->id;
                $attributes['room_id'] = $room->id;

                //Generate the bed code based on:
                //The prefix LIT
                //Then followed by five random number
                $prefix = "LIT";

                $generatedCode = '';

                do {
                    $randomNumbers = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                    $generatedCode = $prefix . '-' . $randomNumbers;

                    // Check if a bed with this code already exists
                    $existingCode = $this->bedRepositoryEloquent->findByCode($generatedCode);
                } while ($existingCode);

                // Add the generated code and other required fields to the attributs
                $attributes['code'] = $generatedCode;
                $attributes['state'] = 'free'; // État par défaut
                
                // Générer un UUID si nécessaire
                if (empty($attributes['uuid'])) {
                    $attributes['uuid'] = \Illuminate\Support\Str::uuid()->toString();
                }

                // S'assurer qu'on ne passe que les champs autorisés (sans hospital_id - multi-tenant)
                $allowedFields = ['uuid', 'room_id', 'patient_id', 'code', 'name', 'state', 'user_id'];
                $attributes = array_intersect_key($attributes, array_flip($allowedFields));
                
                // Double vérification : retirer hospital_id si présent
                unset($attributes['hospital_id']);

                $item = $this->bedRepositoryEloquent->create($attributes);
                return $item;
            });

            $item = $item->fresh();

            return new BedResource($item);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Erreur de validation - retourner les erreurs de validation
            return reponse_json_transform([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            // Erreur SQL - logger mais ne pas exposer les détails à l'utilisateur
            Log::error("Erreur SQL lors de l'ajout d'un lit: " . $e->getMessage(), [
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'attributes' => $request->all()
            ]);
            
            return reponse_json_transform([
                'message' => 'Une erreur est survenue lors de l\'ajout du lit. Veuillez réessayer ou contacter l\'administrateur.'
            ], 500);
        } catch (\Exception $e) {
            // Autres erreurs - logger mais ne pas exposer les détails techniques
            Log::error("Erreur lors de l'ajout d'un lit: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'attributes' => $request->all()
            ]);
            
            // Message générique pour l'utilisateur
            return reponse_json_transform([
                'message' => 'Une erreur est survenue lors de l\'ajout du lit. Veuillez réessayer.'
            ], 500);
        }
    }
    
    /**
     * Update the specified resource in storage.
     * @param BedUpdateRequest $request
     * @return BedResource
     */
    public function update(BedUpdateRequest $request, $uuid)
    {
        try {
            $item = $this->bedRepositoryEloquent->findByUuid($uuid)->first();

            if (!$item) {
                return reponse_json_transform(['message' => 'Lit non trouvé'], 404);
            }

            $attributes = $request->all();

            $room = $this->roomRepositoryEloquent
                ->with(['beds' => function ($query) {
                    $query->whereNull('deleted_at');
                }])
                ->findByUuid($attributes['room_id'])
                ->first();

            if (!$room) {
                return reponse_json_transform(['message' => 'Salle non trouvée'], 404);
            }

            // Nettoyer les attributs : retirer hospital_id (multi-tenant, une base par hôpital)
            // Le wrapper se charge du basculement entre les bases de données
            unset($attributes['hospital_id']);
            
            $attributes['user_id'] = auth()->user()->id;
            $attributes['room_id'] = $room->id;

            // Limite absolue de 10 lits par salle (prioritaire sur la capacité définie)
            $maxCapacity = 10;
            
            // Exclure le lit actuel du comptage si on change juste de salle
            // Si on modifie le lit dans la même salle, on ne compte pas ce lit
            // Si on change de salle, on vérifie que la nouvelle salle a de la place
            // Exclure aussi les lits soft-deleted
            $currentBedCount = $room->beds
                ->where('id', '!=', $item->id)
                ->whereNull('deleted_at')
                ->count();
            
            // Vérifier d'abord la limite absolue de 10 lits (prioritaire)
            if ($currentBedCount >= $maxCapacity) {
                $message = sprintf(
                    'Impossible de modifier ce lit. La limite maximale de %d lits par salle est atteinte. Actuellement : %d lit(s) dans la salle "%s".',
                    $maxCapacity,
                    $currentBedCount,
                    $room->name ?? 'cette salle'
                );
                return reponse_json_transform(['message' => $message], 400);
            }

            // Vérifier que le nombre total de lits ne dépassera pas 10 après modification (limite absolue)
            // Si on change de salle, vérifier que la nouvelle salle n'aura pas plus de 10 lits
            if (($currentBedCount + 1) > $maxCapacity) {
                $message = sprintf(
                    'Impossible de modifier ce lit. La limite maximale de %d lits par salle serait dépassée. Actuellement : %d lit(s) dans la salle "%s".',
                    $maxCapacity,
                    $currentBedCount,
                    $room->name ?? 'cette salle'
                );
                return reponse_json_transform(['message' => $message], 400);
            }
    
            // S'assurer qu'on ne passe que les champs autorisés (sans hospital_id - multi-tenant)
            $allowedFields = ['uuid', 'room_id', 'patient_id', 'code', 'name', 'state', 'user_id'];
            $attributes = array_intersect_key($attributes, array_flip($allowedFields));
            
            // Double vérification : retirer hospital_id si présent
            unset($attributes['hospital_id']);
            
            $item = $this->bedRepositoryEloquent->update($attributes, $item->id);
            $item = $item->fresh();

            return new BedResource($item);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return reponse_json_transform([
                'message' => 'Erreur de validation',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error("Erreur SQL lors de la modification d'un lit: " . $e->getMessage(), [
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'attributes' => $request->all()
            ]);
            
            return reponse_json_transform([
                'message' => 'Une erreur est survenue lors de la modification du lit. Veuillez réessayer ou contacter l\'administrateur.'
            ], 500);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la modification d'un lit: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'attributes' => $request->all()
            ]);
            
            return reponse_json_transform([
                'message' => 'Une erreur est survenue lors de la modification du lit. Veuillez réessayer ou contacter l\'administrateur.'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param BedDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(BedDeleteRequest $request, $uuid)
    {
        try {
            // Charger le lit avec la relation currentStay pour vérifier les séjours actifs
            $item = $this->bedRepositoryEloquent->findByUuid($uuid)->first();

            if (!$item) {
                return reponse_json_transform(['message' => 'Lit non trouvé'], 404);
            }

            // Charger la relation currentStay si elle n'est pas déjà chargée
            if (!$item->relationLoaded('currentStay')) {
                $item->load('currentStay');
            }

            // Vérifier si le lit est actuellement occupé (state = 'busy')
            if ($item->state === 'busy') {
                return reponse_json_transform([
                    'message' => 'Impossible de supprimer ce lit ! Il est actuellement occupé par un patient. Veuillez d\'abord libérer le lit en enregistrant la sortie du patient.'
                ], 400);
            }

            // Vérifier s'il y a un séjour actif (currentStay avec end_occupation_date = null)
            $currentStay = $item->currentStay;
            if ($currentStay && $currentStay->end_occupation_date === null) {
                return reponse_json_transform([
                    'message' => 'Impossible de supprimer ce lit ! Il y a un séjour actif associé à ce lit. Veuillez d\'abord clôturer le séjour du patient en enregistrant sa sortie.'
                ], 400);
            }

            // Si le lit est libre et n'a pas de séjour actif, on peut le supprimer
            // (soft delete pour conserver l'historique des séjours passés)
            $this->bedRepositoryEloquent->delete($item->id);
            
            $data = ["message" => __("Lit supprimé avec succès !")];
            return reponse_json_transform($data);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la suppression d'un lit: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'uuid' => $uuid
            ]);
            
            return reponse_json_transform([
                'message' => 'Une erreur est survenue lors de la suppression du lit. Veuillez réessayer ou contacter l\'administrateur.'
            ], 500);
        }
    }    

    //Not yet verified
    public function affectPatient($bedUuid, $patientUuid)
    {
        try {
            $bed = $this->bedRepositoryEloquent->findByUuid($bedUuid)->first();

            if (!$bed) {
                return reponse_json_transform(['message' => 'Lit non trouvé'], 404);
            }

            $patient = $this->patienteRepositoryEloquent->findByUuid($patientUuid)->first();
            if (!$patient) {
                return reponse_json_transform(['message' => 'Patient non trouvé'], 404);
            }

            // Nettoyer les attributs : retirer hospital_id (multi-tenant, une base par hôpital)
            unset($attributes['hospital_id']);
            
            $attributes['patient_id'] = $patient->id;
            $attributes['state'] = 'busy';
    
            $bed = $this->bedRepositoryEloquent->update($attributes, $bed->id);
            $bed = $bed->fresh();

            return new BedResource($bed);
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }
    }

    public function countCurrentlyHospitalizedPatients()
    {
        try {
            // Retrieve all beds
            $beds = Bed::all();

            // Initialize a variable to keep track of the total count
            $totalCurrentlyHospitalizedPatients = 0;

            // Iterate over each bed
            foreach ($beds as $bed) {
                // Check if the bed is currently occupied (based on the 'state' attribute)
                if ($bed->state === 'busy') {
                    // Increment the count for currently hospitalized patients
                    $totalCurrentlyHospitalizedPatients++;
                }
            }
          
            return response()->json(['total_currently_hospitalized_patients' => $totalCurrentlyHospitalizedPatients], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function getAvailableBeds(Request $request)
    {
        try {
            // On filtre par service si fourni, et on prend les lits "free"
            $query = Bed::where('state', 'free');
            
            // Filtrer par service si fourni
            if ($request->has('service_id') && $request->service_id) {
                $serviceId = is_numeric($request->service_id) ? (int)$request->service_id : $request->service_id;
                $query->whereHas('room', function($q) use ($serviceId) {
                    $q->where('services_id', $serviceId);
                });
                
                Log::info('DEBUG getAvailableBeds - Filtrage par service', [
                    'service_id' => $serviceId,
                    'service_id_type' => gettype($serviceId)
                ]);
            } else {
                // Si aucun service_id fourni, on charge tous les lits libres
                // mais on s'assure que la chambre a un service assigné
                $query->whereHas('room', function($q) {
                    $q->whereNotNull('services_id');
                });
            }
            
            // Charger la relation room avec son service
            $beds = $query->with(['room.service'])->get();

            Log::info('DEBUG getAvailableBeds - Nombre de lits trouvés', [
                'count' => $beds->count(),
                'first_bed_id' => $beds->first() ? $beds->first()->id : null,
                'first_bed_code' => $beds->first() ? $beds->first()->code : null,
            ]);

            // Utiliser BedResource pour formater les données de manière cohérente
            // BedResource::collection() retourne un ResourceCollection qui sera automatiquement sérialisé
            $bedsResource = BedResource::collection($beds);
            
            // resolve() retourne un tableau des ressources transformées
            $bedsArray = $bedsResource->resolve();

            Log::info('DEBUG getAvailableBeds - Après resolve()', [
                'is_array' => is_array($bedsArray),
                'count' => is_array($bedsArray) ? count($bedsArray) : 'N/A',
                'first_bed_structure' => is_array($bedsArray) && count($bedsArray) > 0 ? array_keys($bedsArray[0]) : [],
                'first_bed_id' => is_array($bedsArray) && count($bedsArray) > 0 ? ($bedsArray[0]['id'] ?? 'N/A') : 'N/A',
            ]);

            // reponse_json_transform encapsule dans ['data' => ...]
            // On passe directement le tableau pour obtenir {data: [bed1, bed2, ...]}
            $response = reponse_json_transform($bedsArray);
            
            Log::info('DEBUG getAvailableBeds - Structure de la réponse finale', [
                'response_structure' => json_encode($response->getData(true), JSON_PRETTY_PRINT),
            ]);
            
            return $response;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des lits disponibles: ' . $e->getMessage());
            return reponse_json_transform([
                'data' => [],
                'message' => 'Erreur lors de la récupération des lits disponibles'
            ], 500);
        }
    }
}
