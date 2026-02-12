<?php

namespace Modules\Patient\Http\Controllers\Api\V1;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Modules\Patient\Entities\Patiente;
use App\Repositories\UserRepositoryEloquent;
use Modules\Patient\Http\Resources\PatienteResource;

use Modules\Patient\Http\Resources\PatientesResource;
use Modules\Patient\Http\Controllers\PatientController;
use Modules\Patient\Http\Requests\PatienteIndexRequest;
use Modules\Patient\Http\Requests\PatienteStoreRequest;
use Modules\Patient\Http\Requests\PatienteUpdateRequest;
use Modules\Patient\Repositories\PatienteRepositoryEloquent;
use Modules\Administration\Repositories\PackRepositoryEloquent;
use Modules\Administration\Repositories\PaysRepositoryEloquent;
use Modules\Patient\Http\Requests\PatientInsuranceStoreRequest;
use Modules\Patient\Http\Requests\PatientInsuranceUpdateRequest;
use Modules\Administration\Repositories\CommuneRepositoryEloquent;
use Modules\Patient\Repositories\PatientInsuranceRepositoryEloquent;
use Modules\Administration\Repositories\DepartementRepositoryEloquent;
use Modules\Patient\Http\Controllers\Api\V1\PatientInsuranceController;
use Modules\Administration\Repositories\ArrondissementRepositoryEloquent;

class PatienteController extends PatientController
{

    /**
     * @var PostRepository
     */
    protected $patienteRepositoryEloquent, $arrondissementRepositoryEloquent,
        $userRepositoryEloquent, $departementRepositoryEloquent, $communeRepositoryEloquent,
        $paysRepositoryEloquent, $patientInsuranceRepositoryEloquent, $packRepositoryEloquent;

    //  $patientInsuranceRepositoryEloquent;

    public function __construct(
        PatienteRepositoryEloquent $patienteRepositoryEloquent,
        ArrondissementRepositoryEloquent $arrondissementRepositoryEloquent,
        UserRepositoryEloquent $userRepositoryEloquent,
        DepartementRepositoryEloquent $departementRepositoryEloquent,
        CommuneRepositoryEloquent $communeRepositoryEloquent,
        PaysRepositoryEloquent $paysRepositoryEloquent,
        PatientInsuranceRepositoryEloquent $patientInsuranceRepositoryEloquent,
        PackRepositoryEloquent $packRepositoryEloquent,
        // PatientInsuranceRepositoryEloquent $patientInsuranceRepositoryEloquent
    ) {
        parent::__construct();
        $this->patienteRepositoryEloquent = $patienteRepositoryEloquent;
        $this->arrondissementRepositoryEloquent = $arrondissementRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
        $this->departementRepositoryEloquent = $departementRepositoryEloquent;
        $this->communeRepositoryEloquent = $communeRepositoryEloquent;
        $this->paysRepositoryEloquent = $paysRepositoryEloquent;
        $this->patientInsuranceRepositoryEloquent = $patientInsuranceRepositoryEloquent;
        $this->packRepositoryEloquent = $packRepositoryEloquent;
        // $this->patientInsuranceRepositoryEloquent = $patientInsuranceRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(PatienteIndexRequest $request)
    {
        // OPTIMISATION : Pagination avec paramètre personnalisable (réduit à 20 par défaut pour meilleure performance)
        $perPage = $request->input('per_page', min($this->nombrePage, 20));
        $perPage = min($perPage, 50); // Limiter à 50 pour éviter les surcharges
        
        // OPTIMISATION : Cache côté serveur pour améliorer les performances
        $hospitalId = auth()->check() ? auth()->user()->hospital_id : app('hospital_id');
        $cacheKey = 'patients_list_' . $hospitalId . '_' . $perPage;
        $cacheTTL = 60; // 1 minute de cache
        
        // OPTIMISATION : Option pour désactiver le cache (debug via paramètre ?no_cache=1)
        $useCache = !$request->has('no_cache');
        
        // OPTIMISATION : Mesurer le temps d'exécution pour diagnostiquer les problèmes de performance
        $startTime = microtime(true);
        
        try {
            if ($useCache && Cache::has($cacheKey)) {
                // Cache hit - très rapide
                $donnees = Cache::get($cacheKey);
                $totalTime = microtime(true) - $startTime;
                Log::debug("Patients chargés depuis le cache", [
                    'time' => round($totalTime, 3) . 's'
                ]);
            } else {
                // Cache miss ou désactivé - exécuter la requête
                $queryStart = microtime(true);
                
                $donnees = $this->patienteRepositoryEloquent
                    ->select([
                        'id', 'uuid', 'ipp', 'lastname', 'firstname', 'age', 
                        'phone', 'maison', 'gender', 'email', 'created_at'
                    ])
                    ->orderBy('id', 'desc') // Utiliser id au lieu de created_at pour meilleure performance (index primaire)
                    ->paginate($perPage);
                
                $queryTime = microtime(true) - $queryStart;
                Log::info("Requête patients exécutée", [
                    'query_time' => round($queryTime, 3) . 's',
                    'per_page' => $perPage,
                    'count' => $donnees->total(),
                    'from_cache' => false
                ]);
                
                // Mettre en cache uniquement si activé
                if ($useCache) {
                    Cache::put($cacheKey, $donnees, $cacheTTL);
                }
            }
            
            $totalTime = microtime(true) - $startTime;
            
            // Logger uniquement si le temps est anormalement long
            if ($totalTime > 2) {
                Log::warning("Chargement patients lent", [
                    'total_time' => round($totalTime, 3) . 's',
                    'per_page' => $perPage,
                    'from_cache' => $useCache && Cache::has($cacheKey)
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error("Erreur lors du chargement des patients", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
            
        return new PatientesResource($donnees);
    }

    public function search($request)
    {
        // OPTIMISATION : Limiter la longueur de la recherche pour éviter les requêtes trop lourdes
        $searchTerm = substr($request, 0, 100);
        
        // OPTIMISATION : Pagination avec paramètre personnalisable
        $perPage = request()->input('per_page', $this->nombrePage);
        $perPage = min($perPage, 100);
        
        // OPTIMISATION : Sélection uniquement des colonnes nécessaires
        // Utilisation de where avec closure pour éviter les problèmes avec orWhere et le Global Scope
        $donnees = $this->patienteRepositoryEloquent
            ->select([
                'id', 'uuid', 'ipp', 'lastname', 'firstname', 'age', 
                'phone', 'maison', 'gender', 'email', 'created_at'
            ])
            ->where(function ($query) use ($searchTerm) {
                $query->where('lastname', 'like', "%{$searchTerm}%")
                    ->orWhere('firstname', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%")
                    ->orWhere('ipp', 'like', "%{$searchTerm}%")
                    ->orWhere('phone', 'like', "%{$searchTerm}%")
                    ->orWhere('gender', 'like', "%{$searchTerm}%")
                    ->orWhere('nom_marital', 'like', "%{$searchTerm}%");
            })
            ->orderBy('id', 'desc') // Utiliser id au lieu de created_at pour meilleure performance
            ->paginate($perPage);
        
        return new PatientesResource($donnees);
    }

    /**
     * Display resource.
     *
     * @return Response
     */
    public function show($uuid)
    {
        // VÉRIFICATION : Le patient existe-t-il ?
        $item = $this->patienteRepositoryEloquent
            ->with(['pays', 'departement', 'commune', 'arrondissement', 'patientInsurances.pack'])
            ->findByUuidOrFail($uuid)
            ->first();
            
        if (!$item) {
            return response()->json([
                'message' => 'Patient non trouvé.',
                'error' => 'Le patient avec cet UUID n\'existe pas.'
            ], 404);
        }
        
        return new PatienteResource($item);
    }

    /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(PatienteStoreRequest $request)
    {
        // Récupérez toutes les données du formulaire
        $attributs = $request->all();

        // VÉRIFICATIONS AVANT INSERTION
        
        // 1. Vérification des doublons (nom, prénom, date de naissance)
        if (!isset($attributs['force_create']) || !$attributs['force_create']) {
            $duplicates = $this->patienteRepositoryEloquent
                ->where('lastname', $attributs['lastname'])
                ->where('firstname', $attributs['firstname'])
                ->where('date_birth', $attributs['date_birth'])
                ->get();

            if ($duplicates->count() > 0) {
                return response()->json([
                    'message' => 'Des patients similaires existent déjà.',
                    'duplicates' => PatientesResource::collection($duplicates),
                    'suggestion' => 'Voulez-vous quand même créer ce dossier ?'
                ], 409);
            }
        }

        // 2. Vérification de l'existence des relations (pays, département, commune, arrondissement)
        if (isset($attributs['departements_id']) && $attributs['departements_id']) {
            $departement = $this->departementRepositoryEloquent->find($attributs['departements_id']);
            if (!$departement) {
                return response()->json([
                    'message' => 'Le département sélectionné n\'existe pas.',
                    'errors' => ['departements_id' => 'Département invalide']
                ], 422);
            }
        }

        if (isset($attributs['communes_id']) && $attributs['communes_id']) {
            $commune = $this->communeRepositoryEloquent->find($attributs['communes_id']);
            if (!$commune) {
                return response()->json([
                    'message' => 'La commune sélectionnée n\'existe pas.',
                    'errors' => ['communes_id' => 'Commune invalide']
                ], 422);
            }
            
            // Vérifier que la commune appartient au département sélectionné
            if (isset($attributs['departements_id']) && $commune->departements_id != $attributs['departements_id']) {
                return response()->json([
                    'message' => 'La commune sélectionnée n\'appartient pas au département choisi.',
                    'errors' => ['communes_id' => 'Commune incompatible avec le département']
                ], 422);
            }
        }

        if (isset($attributs['arrondissements_id']) && $attributs['arrondissements_id']) {
            $arrondissement = $this->arrondissementRepositoryEloquent->find($attributs['arrondissements_id']);
            if (!$arrondissement) {
                return response()->json([
                    'message' => 'L\'arrondissement sélectionné n\'existe pas.',
                    'errors' => ['arrondissements_id' => 'Arrondissement invalide']
                ], 422);
            }
            
            // Vérifier que l'arrondissement appartient à la commune sélectionnée
            if (isset($attributs['communes_id']) && $arrondissement->communes_id != $attributs['communes_id']) {
                return response()->json([
                    'message' => 'L\'arrondissement sélectionné n\'appartient pas à la commune choisie.',
                    'errors' => ['arrondissements_id' => 'Arrondissement incompatible avec la commune']
                ], 422);
            }
        }

        // 3. Vérification de l'unicité de l'email si fourni
        if (isset($attributs['email']) && !empty($attributs['email'])) {
            $existingPatient = $this->patienteRepositoryEloquent
                ->where('email', $attributs['email'])
                ->first();
            
            if ($existingPatient) {
                return response()->json([
                    'message' => 'Un patient avec cet email existe déjà.',
                    'errors' => ['email' => 'Email déjà utilisé']
                ], 422);
            }
        }

        // 4. Vérification de l'unicité du téléphone si fourni
        if (isset($attributs['phone']) && !empty($attributs['phone'])) {
            $existingPatient = $this->patienteRepositoryEloquent
                ->where('phone', $attributs['phone'])
                ->first();
            
            if ($existingPatient) {
                return response()->json([
                    'message' => 'Un patient avec ce numéro de téléphone existe déjà.',
                    'errors' => ['phone' => 'Téléphone déjà utilisé']
                ], 422);
            }
        }

        // Créez un tableau pour les données du patient
        $patientData = [
            'lastname' => $attributs['lastname'],
            'firstname' => $attributs['firstname'],
            'date_birth' => $attributs['date_birth'],
            'age' => $attributs['age'],
            'maison' => $attributs['maison'] ?? null,
            'phone' => $attributs['phone'],
            'email' => $attributs['email'] ?? null,
            'whatsapp' => $attributs['whatsapp'] ?? null,
            'profession' => $attributs['profession'] ?? null,
            'gender' => $attributs['gender'],
            'emergency_contac' => $attributs['emergency_contac'],
            'marital_status' => $attributs['marital_status'] ?? null,
            'autre' => $attributs['autre'] ?? null,
            'nom_marital' => $attributs['nom_marital'] ?? null,
            'code_postal' => $attributs['code_postal'] ?? null,
            'nom_pere' => $attributs['nom_pere'] ?? null,
            'phone_pere' => $attributs['phone_pere'] ?? null,
            'nom_mere' => $attributs['nom_mere'] ?? null,
            'phone_mere' => $attributs['phone_mere'] ?? null,
            'quartier' => $attributs['quartier'] ?? null,
            'pays_id' => $attributs['pays_id'] ?? 1,
            'departements_id' => $attributs['departements_id'] ?? null,
            'communes_id' => $attributs['communes_id'] ?? null,
            'arrondissements_id' => $attributs['arrondissements_id'] ?? null,
            'users_id' => auth()->id() ?? 1,
        ];

        try {
            $patient = $this->patienteRepositoryEloquent->create($patientData);
            $patient = $patient->fresh(['pays', 'departement', 'commune', 'arrondissement']);

            return new PatienteResource($patient);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la création du patient: " . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la création du patient.',
                'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue'
            ], 500);
        }
    }



    /**
     * Update a resource.
     *
     * @return Response
     */
    // public function update(PatienteUpdateRequest $request, $uuid)
    // {
    //     $item = $this->patienteRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
    //     $attributs = $request->all();
    //     $item = $this->patienteRepositoryEloquent->update($attributs, $item->id);
    //     $item = $item->fresh();
    //     return new PatienteResource($item);
    // }
    public function update(PatienteUpdateRequest $request, $uuid)
    {
        // VÉRIFICATION : Le patient existe-t-il ?
        $patient = $this->patienteRepositoryEloquent->findByUuidOrFail($uuid)->first();
        
        if (!$patient) {
            return response()->json([
                'message' => 'Patient non trouvé.',
                'error' => 'Le patient avec cet UUID n\'existe pas.'
            ], 404);
        }

        $attributs = $request->all();

        // VÉRIFICATIONS AVANT MODIFICATION
        
        // 1. Vérification de l'existence des relations (département, commune, arrondissement)
        if (isset($attributs['departements_id']) && $attributs['departements_id']) {
            $departement = $this->departementRepositoryEloquent->find($attributs['departements_id']);
            if (!$departement) {
                return response()->json([
                    'message' => 'Le département sélectionné n\'existe pas.',
                    'errors' => ['departements_id' => 'Département invalide']
                ], 422);
            }
        }

        if (isset($attributs['communes_id']) && $attributs['communes_id']) {
            $commune = $this->communeRepositoryEloquent->find($attributs['communes_id']);
            if (!$commune) {
                return response()->json([
                    'message' => 'La commune sélectionnée n\'existe pas.',
                    'errors' => ['communes_id' => 'Commune invalide']
                ], 422);
            }
            
            // Vérifier que la commune appartient au département sélectionné
            if (isset($attributs['departements_id']) && $commune->departements_id != $attributs['departements_id']) {
                return response()->json([
                    'message' => 'La commune sélectionnée n\'appartient pas au département choisi.',
                    'errors' => ['communes_id' => 'Commune incompatible avec le département']
                ], 422);
            }
        }

        if (isset($attributs['arrondissements_id']) && $attributs['arrondissements_id']) {
            $arrondissement = $this->arrondissementRepositoryEloquent->find($attributs['arrondissements_id']);
            if (!$arrondissement) {
                return response()->json([
                    'message' => 'L\'arrondissement sélectionné n\'existe pas.',
                    'errors' => ['arrondissements_id' => 'Arrondissement invalide']
                ], 422);
            }
            
            // Vérifier que l'arrondissement appartient à la commune sélectionnée
            if (isset($attributs['communes_id']) && $arrondissement->communes_id != $attributs['communes_id']) {
                return response()->json([
                    'message' => 'L\'arrondissement sélectionné n\'appartient pas à la commune choisie.',
                    'errors' => ['arrondissements_id' => 'Arrondissement incompatible avec la commune']
                ], 422);
            }
        }

        // 2. Vérification de l'unicité de l'email si modifié (sauf pour le patient actuel)
        if (isset($attributs['email']) && !empty($attributs['email']) && $attributs['email'] !== $patient->email) {
            $existingPatient = $this->patienteRepositoryEloquent
                ->where('email', $attributs['email'])
                ->where('id', '!=', $patient->id)
                ->first();
            
            if ($existingPatient) {
                return response()->json([
                    'message' => 'Un autre patient avec cet email existe déjà.',
                    'errors' => ['email' => 'Email déjà utilisé']
                ], 422);
            }
        }

        // 3. Vérification de l'unicité du téléphone si modifié (sauf pour le patient actuel)
        if (isset($attributs['phone']) && !empty($attributs['phone']) && $attributs['phone'] !== $patient->phone) {
            $existingPatient = $this->patienteRepositoryEloquent
                ->where('phone', $attributs['phone'])
                ->where('id', '!=', $patient->id)
                ->first();
            
            if ($existingPatient) {
                return response()->json([
                    'message' => 'Un autre patient avec ce numéro de téléphone existe déjà.',
                    'errors' => ['phone' => 'Téléphone déjà utilisé']
                ], 422);
            }
        }

        // 4. Vérification de la date de décès (doit être après la date de naissance)
        if (isset($attributs['date_deces']) && !empty($attributs['date_deces'])) {
            $dateNaissance = new \DateTime($patient->date_birth);
            $dateDeces = new \DateTime($attributs['date_deces']);
            
            if ($dateDeces < $dateNaissance) {
                return response()->json([
                    'message' => 'La date de décès ne peut pas être antérieure à la date de naissance.',
                    'errors' => ['date_deces' => 'Date de décès invalide']
                ], 422);
            }
        }

        // Préparation des données pour la mise à jour
        $patientData = [
            'lastname' => $attributs['lastname'],
            'firstname' => $attributs['firstname'],
            'date_birth' => $attributs['date_birth'],
            'age' => $attributs['age'],
            'maison' => $attributs['maison'] ?? $patient->maison,
            'phone' => $attributs['phone'],
            'email' => $attributs['email'] ?? $patient->email,
            'whatsapp' => $attributs['whatsapp'] ?? $patient->whatsapp,
            'profession' => $attributs['profession'] ?? $patient->profession,
            'gender' => $attributs['gender'],
            'emergency_contac' => $attributs['emergency_contac'] ?? $patient->emergency_contac,
            'marital_status' => $attributs['marital_status'] ?? $patient->marital_status,
            'autre' => $attributs['autre'] ?? $patient->autre,
            'nom_marital' => $attributs['nom_marital'] ?? $patient->nom_marital,
            'date_deces' => $attributs['date_deces'] ?? $patient->date_deces,
            'code_postal' => $attributs['code_postal'] ?? $patient->code_postal,
            'nom_pere' => $attributs['nom_pere'] ?? $patient->nom_pere,
            'nom_mere' => $attributs['nom_mere'] ?? $patient->nom_mere,
            'phone_pere' => $attributs['phone_pere'] ?? $patient->phone_pere,
            'phone_mere' => $attributs['phone_mere'] ?? $patient->phone_mere,
            'quartier' => $attributs['quartier'] ?? $patient->quartier,
            'pays_id' => $attributs['pays_id'] ?? $patient->pays_id ?? 1,
            'departements_id' => $attributs['departements_id'] ?? $patient->departements_id,
            'communes_id' => $attributs['communes_id'] ?? $patient->communes_id,
            'arrondissements_id' => $attributs['arrondissements_id'] ?? $patient->arrondissements_id,
        ];

        try {
            $this->patienteRepositoryEloquent->update($patientData, $patient->id);
            $patient = $patient->fresh(['pays', 'departement', 'commune', 'arrondissement', 'patientInsurances.pack']);

            return new PatienteResource($patient);
        } catch (\Exception $e) {
            Log::error("Erreur lors de la modification du patient: " . $e->getMessage());
            return response()->json([
                'message' => 'Erreur lors de la modification du patient.',
                'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy($uuid)
    {
        $patiente = $this->patienteRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->patienteRepositoryEloquent->delete($patiente->id);

        $data = [
            "message" => __("Patient supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }

    public function countPatients()
    {
        $patientsCount = Patiente::count();
        
        return response()->json($patientsCount, 200);
    }

    /**
     * Récupère les détails d'un patient par UUID
     * 
     * @param string $uuid UUID du patient
     * @return Response
     */
    public function detailpatient($uuid)
    {
        // VÉRIFICATION : Le patient existe-t-il ?
        $patient = $this->patienteRepositoryEloquent
            ->with(['pays', 'departement', 'commune', 'arrondissement', 'patientInsurances.pack'])
            ->findByUuid($uuid)
            ->first();

        if (!$patient) {
            return response()->json([
                'message' => 'Patient non trouvé.',
                'error' => 'Le patient avec cet UUID n\'existe pas.'
            ], 404);
        }

        return new PatienteResource($patient);
    }
}
