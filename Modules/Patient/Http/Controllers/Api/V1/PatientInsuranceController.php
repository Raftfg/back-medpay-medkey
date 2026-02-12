<?php

namespace Modules\Patient\Http\Controllers\Api\V1;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
// use App\Repositories\PackRepositoryEloquent;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Administration\Entities\Pack;

use Modules\Patient\Entities\PatientInsurance;
use Modules\Patient\Http\Resources\PatienteResource;
use Modules\Patient\Http\Controllers\PatientController;
use Modules\Patient\Http\Requests\PatienteStoreRequest;
use Modules\Patient\Http\Resources\PatientInsuranceResource;
use Modules\Patient\Repositories\PatienteRepositoryEloquent;
use Modules\Patient\Http\Resources\PatientInsurancesResource;
use Modules\Administration\Repositories\PackRepositoryEloquent;
use Modules\Patient\Http\Requests\PatientInsuranceIndexRequest;
use Modules\Patient\Http\Requests\PatientInsuranceStoreRequest;
use Modules\Patient\Http\Requests\PatientInsuranceDeleteRequest;
use Modules\Patient\Http\Requests\PatientInsuranceUpdateRequest;
use Modules\Patient\Repositories\PatientInsuranceRepositoryEloquent;


class PatientInsuranceController extends PatientController
{

    /**
     * @var PostRepository
     */
    protected $patientInsuranceRepositoryEloquent, $packRepositoryEloquent, $patienteRepositoryEloquent;

    public function __construct(PatientInsuranceRepositoryEloquent $patientInsuranceRepositoryEloquent, PatienteRepositoryEloquent $patienteRepositoryEloquent, PackRepositoryEloquent $packRepositoryEloquent)
    // public function __construct(PatientInsuranceRepositoryEloquent $patientInsuranceRepositoryEloquent, PackRepositoryEloquent $packRepositoryEloquent, PatienteRepositoryEloquent $patienteRepositoryEloquent)
    {
        parent::__construct();
        $this->patientInsuranceRepositoryEloquent = $patientInsuranceRepositoryEloquent;
        // $this->packRepositoryEloquent = $packRepositoryEloquent;
        $this->packRepositoryEloquent = $packRepositoryEloquent;
        $this->patienteRepositoryEloquent = $patienteRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(PatientInsuranceIndexRequest $request)
    {
        $donnees = $this->patientInsuranceRepositoryEloquent->paginate($this->nombrePage);
        return new PatientInsurancesResource($donnees);
    }

    /**
     * Create a resource.
     *
     * @return Response
     */
    public function show(PatientInsuranceIndexRequest $request, $uuid)
    {
        $item = $this->patientInsuranceRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new PatientInsuranceResource($item);
    }

    public function getInsuranceByPatient($uuid)
    {
        // $insuranceId = $request->input('insuranceId');
        $patient = $this->patienteRepositoryEloquent->findByUuidOrFail($uuid)->first();
        // \Log::info($patient);
        $Insurance = PatientInsurance::where('patients_id', $patient->id)->get();

        return response()->json($Insurance);
    }
    

    public function store(PatientInsuranceStoreRequest $request)
    {
        $attributs['patients_id'] = $request->patients_id;
        $attributs['pack_id'] = $request->pack_id;

        $item = DB::transaction(function () use ($attributs) {
            
            if (isset($attributs['pack_id'])) {
                $pack = $this->packRepositoryEloquent->findByUuid($attributs['pack_id'])->first();
                $attributs['pack_id'] = $pack->id;
            }


            $item = $this->patientInsuranceRepositoryEloquent->create($attributs);

            return $item;
        });

        $item = $item->fresh();

        return new PatientInsuranceResource($item);
    }
    public function add(PatientInsuranceStoreRequest $request)
    {
        // Récupérez toutes les données du formulaire
        $attributes = $request->all();
        // \Log::info($attributes);


    // Récupérez le patient associé
    $patient = $this->patienteRepositoryEloquent->findByUuid($attributes['patients_id'])->first();
    \Log::info($attributes);
//    // Récupérez le pack associé
//    $pack = $this->packRepositoryEloquent->findByUuid($attributes['pack_id'])->first();
    // Vérifiez si patient  existe
    if (!$patient) {
        return response()->json([
            'success' => false,
            'message' => 'Le patient sélectionné est invalide.',
        ], 400);
    }
    // if (!$pack) {
    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Le pack sélectionné est invalide.',
    //     ], 400);
    // }


    // Mettez à jour l'ID du patient avec l'ID récupéré
    $attributes['patients_id'] = $patient->id;
    // // Mettez à jour l'ID du pack avec l'ID récupéré
    // $attributes['pack_id'] =  $pack->id;

    //Vérifiez si les données des packs sont présentes
    if (!isset($attributes['packpatients']) || empty($attributes['packpatients'])) {
        return response()->json([
            'success' => false,
            'message' => 'Les données des packs patient sont requises et ne peuvent pas être vides.',
        ], 400);
    }

    $patientinsurancesData = $attributes['packpatients'];
    $packpatients = [];

    // Enregistrez chaque pack patient
    foreach ($patientinsurancesData as $patientinsuranceData) {
        $pack = $this->packRepositoryEloquent->findByUuid($patientinsuranceData['pack_id'])->first();
        $patientinsurance = [
            'numero_police' => $patientinsuranceData['numero_police'],
            'date_debut' => $patientinsuranceData['date_debut'],
            'date_fin' => $patientinsuranceData['date_fin'],
            'patients_id' => $attributes['patients_id'],
            'pack_id' => $pack->id,
        ];

        $createdPatientInsurance = $this->patientInsuranceRepositoryEloquent->create($patientinsurance);

        $patientinsurances[] = $createdPatientInsurance->fresh(); // Ajoutez le pack frais au tableau
    }

    return new  PatientInsurancesResource($patientinsurances);
}


    /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(PatientInsuranceUpdateRequest $request, $uuid)
    {
        $item = $this->patientInsuranceRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs['patients_id'] = $request->patients_id;
        $attributs['pack_id'] = $request->pack_id;

        if (isset($attributs['pack_id'])) {
            $pack = $this->packRepositoryEloquent->findByUuid($attributs['pack_id'])->first();
            $attributs['pack_id'] = $pack->id;
        }

        // if (isset($attributs['patients_id'])) {
        //     $patiente = $this->patienteRepositoryEloquent->findByUuid($attributs['patients_id'])->first();
        //     $attributs['patients_id'] = $patiente->id;
        // }
        //department à gérer 

        $item = $this->patientInsuranceRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();
        return new PatientInsuranceResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(PatientInsuranceDeleteRequest $request, $uuid)
    {
        $patientInsurance = $this->patientInsuranceRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->patientInsuranceRepositoryEloquent->delete($patientInsurance->id);

        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }

    public function getPacksByPatient($uuid)
    {
        // Récupérer le patient par son UUID
        $patient = Patient::where('uuid', $uuid)->firstOrFail();

        // Récupérer les informations d'assurance du patient
        $patientInsurances = PatientInsurance::where('patients_id', $patient->id)->get();

        // Récupérer les IDs des assurances associées au patient
        $insuranceIds = $patientInsurances->pluck('insurances_id')->unique();

        // Récupérer les packs associés aux assurances du patient
        $packs = Pack::whereIn('insurances_id', $insuranceIds)->get();

        return response()->json($packs);
    }

   
    
    public function getPackDetailsByPatient($uuid)
{
    try {
        // Récupérer le patient en utilisant l'UUID
        $patient = Patient::where('uuid', $uuid)->firstOrFail();

        // Récupérer les enregistrements de Patient_insurances associés au patient avec les détails du pack et de l'assurance
        $patientInsurances = PatientInsurance::where('patients_id', $patient->id)
            ->with('pack.insurance') // Charger la relation pack avec les détails de l'assurance
            ->get();

        // Initialiser un tableau pour stocker les détails des packs
        $packsDetails = [];

        // Parcourir les enregistrements de Patient_insurances
        foreach ($patientInsurances as $patientInsurance) {
            // Accéder aux détails du pack associé
            $packDetails = [
                'designation' => $patientInsurance->pack->designation,
                'insurance' => $patientInsurance->pack->insurance->name,
                'percentage' => $patientInsurance->pack->percentage,
                'date_debut' => $patientInsurance->date_debut,
                'date_fin' => $patientInsurance->date_fin,
                'numero_police' => $patientInsurance->numero_police,
                // 'uuid'=> $patientInsurance->uuid,
                // 'patients_id'=> $patientInsurance->patients_id,
                // Ajoutez d'autres détails du pack au besoin
            ];

            // Ajouter les détails du pack au tableau
            $packsDetails[] = $packDetails;
        }

        // Retourner les détails des packs en tant que réponse JSON
        return response()->json($packsDetails, 200);
    } catch (\Exception $e) {
        // Gérer l'exception si le patient ou les enregistrements ne sont pas trouvés
        return response()->json(['error' => 'Patient or patient insurances not found.'], 404);
    }
}



}
