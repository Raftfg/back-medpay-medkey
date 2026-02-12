<?php

namespace Modules\Hospitalization\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Hospitalization\Http\Resources\BedPatientResource;
use Modules\Hospitalization\Http\Resources\BedPatientsResource;
use Modules\Hospitalization\Http\Requests\BedPatientIndexRequest;
use Modules\Hospitalization\Http\Requests\BedPatientStoreRequest;
use Modules\Hospitalization\Http\Requests\BedPatientDeleteRequest;
use Modules\Hospitalization\Http\Requests\BedPatientUpdateRequest;
use Modules\Hospitalization\Http\Controllers\HospitalizationController;
use Modules\Acl\Repositories\UserRepository;
use Modules\Patient\Repositories\PatienteRepositoryEloquent;
use Modules\Hospitalization\Repositories\BedPatientRepositoryEloquent;
use Modules\Hospitalization\Repositories\BedRepositoryEloquent;
use Modules\Hospitalization\Entities\BedPatient;
use Modules\Movment\Entities\movment;

class BedPatientController extends HospitalizationController {

    /**
     * @var BedPatientRepositoryEloquent
     */
    protected $bedPatientRepositoryEloquent;

    /**
     * @var BedRepositoryEloquent
     */
    protected $bedRepositoryEloquent;

    /**
     * @var UserRepository
     */
    protected $userRepositoryEloquent;

    /**
     * @var PatienteRepositoryEloquent
     */
    protected $patienteRepositoryEloquent;

    public function __construct(
        BedPatientRepositoryEloquent $bedPatientRepositoryEloquent,
        BedRepositoryEloquent $bedRepositoryEloquent,
        PatienteRepositoryEloquent $patienteRepositoryEloquent,
        UserRepository $userRepositoryEloquent,

    ) {
        parent::__construct();
        $this->bedPatientRepositoryEloquent = $bedPatientRepositoryEloquent;
        $this->bedRepositoryEloquent = $bedRepositoryEloquent;
        $this->patienteRepositoryEloquent = $patienteRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }

    
    /**
     * Return a listing of the resource.
     * @param BedPatientIndexRequest $request
     * @return BedPatientsResource
     */
    public function index(BedPatientIndexRequest $request)
    {
        try {
            $query = BedPatient::query();
            
            // Charger les relations nécessaires pour l'historique
            $query->with(['bed.room.service', 'patient', 'movment']);
            
            // Filtres optionnels
            if ($request->has('status') && $request->status) {
                if ($request->status === 'active') {
                    $query->whereNull('end_occupation_date');
                } elseif ($request->status === 'completed') {
                    $query->whereNotNull('end_occupation_date');
                }
            }
            
            if ($request->has('date_from') && $request->date_from) {
                $query->whereDate('start_occupation_date', '>=', $request->date_from);
            }
            
            if ($request->has('date_to') && $request->date_to) {
                $query->whereDate('start_occupation_date', '<=', $request->date_to);
            }
            
            // Recherche par IPP ou nom patient
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->whereHas('patient', function($q) use ($search) {
                    $q->where('ipp', 'like', "%{$search}%")
                      ->orWhere('lastname', 'like', "%{$search}%")
                      ->orWhere('firstname', 'like', "%{$search}%");
                });
            }
            
            // Trier par date d'entrée décroissante
            $query->orderBy('start_occupation_date', 'desc');
            
            $data = $query->paginate($this->nombrePage);
            return new BedPatientsResource($data);
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la récupération de l'historique d'hospitalisation: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return reponse_json_transform(['message' => 'Erreur lors de la récupération de l\'historique: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the specified resource.
     * @param BedPatientIndexRequest $request
     * @param string $uuid
     * @return BedPatientResource
     */ 
    public function show(BedPatientIndexRequest $request, $uuid) 
    {
        try {
            // Charger toutes les relations nécessaires pour les détails
            $item = $this->bedPatientRepositoryEloquent
                ->with(['bed.room.service', 'patient', 'movment'])
                ->findByUuid($uuid)
                ->first();

            if (!$item) {
                return reponse_json_transform(['message' => 'Historique d\'hospitalisation non trouvé'], 404);
            }

            return new BedPatientResource($item);
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la récupération des détails d'hospitalisation: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param BedPatientStoreRequest $request
     * @return BedPatientResource
     */
    public function store(BedPatientStoreRequest $request)
    {
        // $attributes = $request->all();

        // $mouvement = movment::where('uuid', $attributes['patient_id'])->get();
        // return $mouvement;
        // try {
            $attributes = $request->all();

            $item = DB::transaction(function () use ($attributes) {
                $mouvement = movment::where('uuid', $attributes['patient_id'])->get();
                $bed = $this->bedRepositoryEloquent->findByUuid($attributes['bed_id'] )->first();
                // $patient = $this->patienteRepositoryEloquent->findByUuid($attributes['patient_id'] )->first();

                $number_of_days = $attributes['number_of_days'];
                $start_date = $attributes['start_occupation_date'];
                $end_date = date("Y-m-d", strtotime($start_date . " + " . $number_of_days . " days"));

                $attributes['bed_id'] = $bed->id;
                $attributes['patient_id'] = $mouvement[0]->patients_id;
                $attributes['end_occupation_date'] = $end_date;
                // Need review to store the Auth::user()
                $attributes['user_id'] = 1;

                $bed->update([
                    'patient_id' => $mouvement[0]->patients_id,
                    'state' => 'busy', // You may want to update this dynamically based on Auth::user()
                ]);

                $item = $this->bedPatientRepositoryEloquent->create($attributes);
                return $item;
            });

            $item = $item->fresh();

            return new BedPatientResource($item);
        // } catch (\Exception $e) {
        //     return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        // }
    }
    
    /**
     * Update the specified resource in storage.
     * @param BedPatientUpdateRequest $request
     * @return BedPatientResource
     */
    public function update(BedPatientUpdateRequest $request, $uuid)
    {
        try {
            $item = $this->bedPatientRepositoryEloquent->findByUuid($uuid)->first();

            if (!$item) {
                return reponse_json_transform(['message' => 'Historique d\'hospitalisation non trouvé'], 404);
            }

            $attributes = $request->all();

            $bed = $this->bedRepositoryEloquent->findByUuid($attributes['bed_id'] )->first();
            $patient = $this->patienteRepositoryEloquent->findByUuid($attributes['patient_id'] )->first();

            // Need review to store the Auth::user()
            $attributes['user_id'] = 1;
            $attributes['bed_id'] = $bed->id;
            $attributes['patient_id'] = $patient->id;
    
            $item = $this->bedPatientRepositoryEloquent->update($attributes, $item->id);
            $item = $item->fresh();

            return new BedPatientResource($item);
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param BedPatientDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(BedPatientDeleteRequest $request, $uuid)
    {
        try {
            $item = $this->bedPatientRepositoryEloquent->findByUuid($uuid)->first();

            if (!$item) {
                return reponse_json_transform(['message' => 'Historique d\'hospitalisation non trouvé'], 404);
            }

            // $this->bedPatientRepositoryEloquent->delete($item->id);
            // $data = ["message" => __("Historique d'hospitalisation supprimé avec succès !")];
            // return reponse_json_transform($data);
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }
    }    

    public function getCountHospitalizedPatient()
    {}
}
