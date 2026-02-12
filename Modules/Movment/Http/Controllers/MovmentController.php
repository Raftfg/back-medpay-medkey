<?php

namespace Modules\Movment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Modules\Patient\Entities\Patiente;
use Modules\Administration\Entities\MedicalAct;
use Modules\Administration\Entities\Service;

use Modules\Movment\Entities\Movment;
use Carbon\Carbon;
use Illuminate\Support\Str;


use Illuminate\Support\Facades\DB;

class MovmentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
      try {
        \Log::info('MovmentController::index appelé', [
          'url' => $request->fullUrl(),
          'method' => $request->method(),
          'user' => auth()->check() ? auth()->id() : 'non authentifié',
          'connection' => \DB::connection()->getName(),
        ]);

        // OPTIMISATION: Limiter la pagination pour éviter les surcharges
        $perPage = min((int)request('perPage', 50), 100);
        $q = request('q', '');

        // OPTIMISATION: Limiter la longueur de la recherche
        $searchTerm = substr($q, 0, 100);

        $data = Movment::orderBy('movments.created_at','desc')
        ->join('patients', 'patients.id', '=', 'movments.patients_id')
        ->leftJoin('services', 'services.id', '=', 'movments.active_services_id')
        ->whereNull('movments.releasedate')
        ->where(function($query) use ( $searchTerm ) {
          if (!empty($searchTerm)) {
            $query->where('patients.ipp', 'like', "%{$searchTerm}%")
            ->orWhere('patients.firstname', 'like', "%{$searchTerm}%")
            ->orWhere('patients.lastname', 'like', "%{$searchTerm}%")
            ->orWhere('patients.phone', 'like', "%{$searchTerm}%")
            ->orWhere('services.name', 'like', "%{$searchTerm}%");
          }
        })
        ->select(
          'movments.patients_id',
          'movments.id',
          'movments.iep',
          'services.name as services_name',
          'movments.ipp',
          'movments.uuid as uuid',
          'movments.admission_type',
          'movments.responsible_doctor_id',
          'patients.uuid as patient_uuid',
          'patients.lastname',
          'patients.firstname',
          'patients.date_birth',
          'patients.age',
          'patients.phone',
          'patients.email',
          'patients.gender',
          'movments.arrivaldate',
          'movments.created_at'
        )
        ->paginate($perPage);

        return response()->json([
          'success' => true,
          'data' => $data,
          'message' => 'Liste des venues en cours.'
        ]);
      } catch (\Exception $e) {
        \Log::error('Erreur lors de la récupération des venues: ' . $e->getMessage(), [
          'trace' => $e->getTraceAsString(),
          'request' => $request->all()
        ]);
        return response()->json([
          'success' => false,
          'data' => [],
          'message' => 'Erreur lors de la récupération des venues: ' . $e->getMessage()
        ], 500);
      }
    }


    public function getConsultationMovments(Request $request)
    {

      $service = Service::where('code',$request->service_code)->first();


      if(request('perPage')) { $perPage = request('perPage') ; }else{ $perPage = 50 ;};

      $q = request('q');

      $data = Movment::orderBy('created_at','desc')
      ->join('patients', 'patients.id', '=', 'movments.patients_id')
      ->leftJoin('services', 'services.id', '=', 'movments.active_services_id')
      ->where('movments.active_services_id',$service->id)
      ->whereNull('releasedate')
      ->where(function($query) use ( $q ) {
        $query->where('patients.ipp', 'like', "%$q%")
        ->OrWhere('patients.firstname', 'like', "%$q%")
        ->OrWhere('patients.lastname', 'like', "%$q%")
        ->OrWhere('patients.phone', 'like', "%$q%")
        ->OrWhere('services.name', 'like', "%$q%");
      })->select('patients_id',
      'movments.id as id',
      'movments.iep',
      'services.name as services_name',
      'movments.ipp',
      'movments.uuid as uuid',
      'patients.uuid as patient_uuid',
      'lastname',
      'firstname',
      'date_birth',
      'age',
      'phone',
      'email',
      'gender',
      'arrivaldate',
      'releasedate',
      'movments.created_at')
      ->paginate($perPage);

      return response()->json([
        'success' => true,
        'data' => $data,
        'message' => 'Liste des patients.'
      ]);

    }

    public function getAll(Request $request)
    {
      if(request('perPage')) { $perPage = request('perPage') ; }else{ $perPage = 50 ;};

      $q = request('q');

      $data = Movment::orderBy('created_at','desc')
      ->join('patients', 'patients.id', '=', 'movments.patients_id')
      ->leftJoin('services', 'services.id', '=', 'movments.active_services_id')
      ->where(function($query) use ( $q ) {
        $query->where('patients.ipp', 'like', "%$q%")
        ->OrWhere('patients.firstname', 'like', "%$q%")
        ->OrWhere('patients.lastname', 'like', "%$q%")
        ->OrWhere('patients.phone', 'like', "%$q%")
        ->OrWhere('services.name', 'like', "%$q%");
      })->select('patients_id',
      'movments.id as id',
      'movments.iep',
      'services.name as services_name',
      'movments.ipp',
      'movments.uuid as uuid',
      'lastname',
      'firstname',
      'date_birth',
      'age',
      'phone',
      'email',
      'gender',
      'arrivaldate',
      'releasedate',
      'movments.created_at')
      ->paginate($perPage);

      return response()->json([
        'success' => true,
        'data' => $data,
        'message' => 'Liste des patients.'
      ]);

    }


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
      return view('movment::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

     $request->validate([
       'patient_id' => 'required|numeric',
       'service_id' => 'required|numeric',
       'medical_acts_id' => 'required|numeric',
     ]);

     $existMovment  =   Movment::where('patients_id', $request->patient_id)
     ->where('releasedate',null)->first();
     if($existMovment){
       return response()->json([
        'code' =>302,
        'success' => true,
        'data' => $existMovment,
        'message' => 'Mouvement déja en cours !'
      ]);
     }

     DB::beginTransaction();

     $Movment = Movment::create([
      'patients_id'=> $request->patient_id,
      'iep'=> $this->getIEP(),
      'ipp'=> $this->getIPP($request->patient_id),
      'arrivaldate' => Carbon::now(),
      'incoming_reason' => $request->reason,
      'active_services_id' => $request->service_id
    ]);

     if($Movment){
      // L'isolation est gérée par la connexion à la base de données.
      DB::table('patient_movement_details')->insert([
      'uuid' => Str::uuid(),
      'medical_acts_id'=> $request->medical_acts_id,
      'medical_acts_uuid'=> $this->getActUuid($request->medical_acts_id),
      'medical_acts_qte'=> 1,
      'percentage_patient_insurance' => $this->getPatientPackPpercentage($Movment->id),
      'medical_acts_price'=> $this->getAct($request->medical_acts_id),
      'type'=> "A",
      'services_id'=>  $request->service_id,
      'movments_id'=> $Movment->id
    ]);


    }

    DB::commit();

    $Movment = Movment::find($Movment->id);
    return response()->json([
      'success' => true,
      'data' =>  $Movment,
      'message' => 'Mouvement créé !'
    ]);
  }

  public function getIEP()
  {
   $m = Movment::latest()->first();
   if($m) {
    return $m->iep +1;
  }else{
   return 1;
 }

}

public function getIPP($patient_id)
{
  return Patiente::find($patient_id)->ipp;
}

public function getServiceCode($service_id)
{
  return Service::find($service_id)->code;
}

public function getAct($medical_acts_id)
{
  // medical_acts est partagé entre tous les hôpitaux
  return  DB::table('medical_acts')->where('id',$medical_acts_id)->first()->price;
}

public function getActUuid($medical_acts_id)
{
  // Note: medical_acts n'a pas de hospital_id, mais est partagé entre tous les hôpitaux
  return  DB::table('medical_acts')->where('id',$medical_acts_id)->first()->uuid;
}


public function getProductPrice($product_id)
{
  return  0;
  // DB::table('products')
  /*->join('stock_products', 'products.id', '=', 'stock_products.product_id')
  ->where('products.id', $product_id)->first()->selling_price;*/
}

public function getProductId($product_uuid){
  // Récupération de l'ID du produit via son UUID
  $product = DB::table('products')
      ->where('products.uuid', $product_uuid)
      ->first();
  return $product ? $product->id : null;
}


public function getProductUuid($product_uuid){
  // Récupération de l'UUID du produit via son UUID (vérification d'existence)
  $product = DB::table('products')
      ->where('products.uuid', $product_uuid)
      ->first();
  return $product ? $product->uuid : null;
}


public function getPatientPackPpercentage($movments_id){
  $patient = DB::table('movments')
      ->where('id',$movments_id)
      ->first();
 return 0;

 /* if($patient_id){
    $patient_insurances =  DB::table('patient_insurances')
    ->join('patients', 'patients.id', '=', 'patient_insurances.patients_id')
    ->join('packs', 'packs.id', '=', 'patient_insurances.pack_id')
    ->where('patients.id',$patient_id)
    ->first();
    return $patient_insurances->percentage;
  }else{
    return 0;
  }*/
}





public function show($id)
{
  try {
    // Accepter soit un ID numérique soit un UUID
    $movment = null;
    if (is_numeric($id)) {
      $movment = Movment::where('movments.id', $id)->first();
    } else {
      // C'est probablement un UUID
      $movment = Movment::where('movments.uuid', $id)->first();
    }
    
    if (!$movment) {
      return response()->json([
        'success' => false,
        'message' => 'Mouvement non trouvé'
      ], 404);
    }
    
    // Charger les relations nécessaires
    $movment->load([
      'patient',
      'service',
      'doctor',
      'bedPatients.bed.room.service'
    ]);
    
    // Construire la réponse avec toutes les informations nécessaires
    $data = [
      'id' => $movment->id,
      'uuid' => $movment->uuid,
      'patients_id' => $movment->patients_id,
      'patient_uuid' => $movment->patient ? $movment->patient->uuid : null,
      'iep' => $movment->iep,
      'ipp' => $movment->ipp,
      'active_services_id' => $movment->active_services_id,
      'admission_type' => $movment->admission_type,
      'responsible_doctor_id' => $movment->responsible_doctor_id,
      'incoming_reason' => $movment->incoming_reason,
      'outgoing_reason' => $movment->outgoing_reason,
      'arrivaldate' => $movment->arrivaldate,
      'releasedate' => $movment->releasedate,
      'created_at' => $movment->created_at,
      'patient' => $movment->patient ? [
        'uuid' => $movment->patient->uuid,
        'lastname' => $movment->patient->lastname,
        'firstname' => $movment->patient->firstname,
        'ipp' => $movment->patient->ipp,
        'date_birth' => $movment->patient->date_birth,
        'age' => $movment->patient->age,
        'phone' => $movment->patient->phone,
        'email' => $movment->patient->email,
        'gender' => $movment->patient->gender,
      ] : null,
      'service' => $movment->service ? [
        'id' => $movment->service->id,
        'name' => $movment->service->name,
        'code' => $movment->service->code,
      ] : null,
      'services_name' => $movment->service ? $movment->service->name : null,
      'doctor' => $movment->doctor ? [
        'id' => $movment->doctor->id,
        'uuid' => $movment->doctor->uuid ?? null,
        'name' => $movment->doctor->name ?? null,
        'prenom' => $movment->doctor->prenom ?? null,
        'firstname' => $movment->doctor->firstname ?? $movment->doctor->prenom ?? null,
        'email' => $movment->doctor->email ?? null,
      ] : null,
      'bed_patients' => $movment->bedPatients ? $movment->bedPatients->map(function($bp) {
        return [
          'id' => $bp->id,
          'uuid' => $bp->uuid,
          'bed_id' => $bp->bed_id,
          'start_occupation_date' => $bp->start_occupation_date,
          'end_occupation_date' => $bp->end_occupation_date,
          'bed' => $bp->bed ? [
            'id' => $bp->bed->id,
            'uuid' => $bp->bed->uuid,
            'code' => $bp->bed->code,
            'name' => $bp->bed->name,
            'room' => $bp->bed->room ? [
              'id' => $bp->bed->room->id,
              'name' => $bp->bed->room->name,
              'code' => $bp->bed->room->code,
              'service' => $bp->bed->room->service ? [
                'id' => $bp->bed->room->service->id,
                'name' => $bp->bed->room->service->name,
              ] : null,
            ] : null,
          ] : null,
        ];
      }) : [],
    ];
    
    return response()->json([
      'success' => true,
      'data' => $data,
      'message' => 'Mouvement récupéré avec succès'
    ]);
  } catch (\Exception $e) {
    \Log::error("Erreur lors de la récupération du mouvement: " . $e->getMessage(), [
      'id' => $id,
      'trace' => $e->getTraceAsString()
    ]);
    return response()->json([
      'success' => false,
      'message' => 'Erreur lors de la récupération du mouvement: ' . $e->getMessage()
    ], 500);
  }
}

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        //return view('movment::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */

    public function updateOut(Request $request)
    {

     $request->validate([
       'movments_id' => 'required|numeric',
       'outgoing_reason' => 'required'
     ]);

     $Movment =   Movment::find($request->movments_id);

     $Movment->update([
      'releasedate'=> Carbon::now(),
      'outgoing_reason'=>$request->outgoing_reason
    ]);

     return response()->json([
      'success' => true,
      'data' =>  $Movment,
      'message' => 'Mouvement mise à jour !'
    ]);

   }


   public function update(Request $request,$id)
   {


   }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function getMovmentsByService(Request $request)
    {
     return response()->json([

      'success' => true,
      'data' => Movment::join('service_movments', 'movments.id', '=', 'service_movments.movments_id')
      ->join('patients', 'patients.id', '=', 'movments.patients_id')
      ->where('service_movments.services_id', $request->service_id)
      ->where('movments.releasedate',null)
      ->get(['movments.id','services_id','movments.iep','movments.ipp','lastname','firstname','movments.created_at']),

      'message' => 'Liste des patients.'
    ]);

   }


   public function getMovmentActes(Request $request)
   {
     return response()->json([
      'success' => true,
      'data' => DB::table('medical_acts')
          ->join('patient_movement_details', 'patient_movement_details.medical_acts_id', '=', 'medical_acts.id')
          ->join('movments', 'patient_movement_details.movments_id', '=', 'movments.id')
          ->where('patient_movement_details.movments_id', $request->movment_id)
          ->where('patient_movement_details.type',"A")
          ->get(),

      'message' => 'Liste des actes par mouvement.'
    ]);

   }


   public function getMovmentProducts(Request $request)
   {
     return response()->json([
      'success' => true,
      'data' => DB::table('products')
          ->join('patient_movement_details', 'patient_movement_details.medical_acts_id', '=', 'products.id')
          ->join('movments', 'patient_movement_details.movments_id', '=', 'movments.id')
          ->where('patient_movement_details.movments_id', $request->movment_id)
          ->where('patient_movement_details.type',"P")
          ->get(),

      'message' => 'Liste des actes par mouvement.'
    ]);

   }



   public function storeActe(Request $request)
   {


    $request->validate([
     'movments_id' => 'required|numeric',
     'medical_acts_id' => 'required|numeric',
     'medical_acts_qte' => 'required|numeric',
     'services_id' => 'required|numeric'
   ]);

    DB::beginTransaction();

    // Vérifier que le mouvement existe
    $movment = DB::table('movments')
        ->where('id', $request->movments_id)
        ->first();
    
    if (!$movment) {
        return response()->json(['error' => 'Mouvement non trouvé ou n\'appartient pas à cet hôpital'], 404);
    }

    // L'isolation est gérée par la base de données.
    $movment_detail =  DB::table('patient_movement_details')->insert([
      'uuid' => Str::uuid(),
      'medical_acts_id'=> $request->medical_acts_id,
      'medical_acts_uuid'=> $this->getActUuid($request->medical_acts_id),
      'medical_acts_qte'=>  $request->medical_acts_qte,
      'percentage_patient_insurance' => $this->getPatientPackPpercentage($request->movments_id),
      'medical_acts_price'=> $this->getAct($request->medical_acts_id),
      'type'=> "A",
      'services_id'=>  $request->services_id,
      'movments_id'=> $request->movments_id
    ]);

    DB::commit();


    return response()->json([
      'success' => true,
      'data' => $movment_detail,
      'message' => 'Mouvement créé !'
    ]);


  }

  public function storeProduct(Request $request)
  {

   $request->validate([
     'movments_id' => 'required|numeric',
     'product_id' => 'required',
     'product_qte' => 'required|numeric',
     'services_id' => 'required|numeric'

   ]);

   DB::beginTransaction();

   // Vérifier que le mouvement existe
   $movment = DB::table('movments')
       ->where('id', $request->movments_id)
       ->first();
   
   if (!$movment) {
       return response()->json(['error' => 'Mouvement non trouvé ou n\'appartient pas à cet hôpital'], 404);
   }

   // Recherche par code ou désignation
   $productId = $this->getProductId($request->product_id);
   if (!$productId) {
       return response()->json(['error' => 'Produit non trouvé ou n\'appartient pas à cet hôpital'], 404);
   }

   // L'isolation est gérée par la base de données.
   $movment_detail =  DB::table('patient_movement_details')->insert([
    'uuid' => Str::uuid(),
    'medical_acts_id'=> $productId,
    'medical_acts_uuid'=> $request->product_id,
    'percentage_patient_insurance'=> $this->getPatientPackPpercentage($request->movments_id),
    'medical_acts_qte'=>  $request->product_qte,
    'medical_acts_price'=> $this->getProductPrice($productId),
    'type'=> "P",
    'services_id'=>  $request->services_id,
    'movments_id'=> $request->movments_id
  ]);

   DB::commit();

   return response()->json([
    'success' => true,
    'data' => $movment_detail,
    'message' => 'Mouvement créé !'
  ]);

 }



 public function deleteActe(Request $request)
 {

  $request->validate([
   'act_id' => 'required|numeric'
 ]);

  DB::beginTransaction();

  // Vérifier que le mouvement_detail existe
  $movmentDetail = DB::table('patient_movement_details')
      ->join('movments', 'patient_movement_details.movments_id', '=', 'movments.id')
      ->where('patient_movement_details.id', $request->act_id)
      ->first();
  
  if (!$movmentDetail) {
      return response()->json(['error' => 'Détail de mouvement non trouvé ou n\'appartient pas à cet hôpital'], 404);
  }

  $movment_detail =  DB::table('patient_movement_details')
  ->where('id', $request->act_id)->delete();

  DB::commit();

  return response()->json([
    'success' => true,
    'data' => $movment_detail,
    'message' => 'Mouvement créé !'
  ]);


}




public function getRecord(Request $request)
{

$data = [
    'complaint'=> "",
    'exam'=>"",
    'observation'=>"",
    'reason'=> "",
    'exam'=> "",
    'summary'=>"",
    'operator'=>"",
    'services_id'=> "",
    'movments_id'=> ""
];


// Vérifier que le mouvement existe
$movment = DB::table('movments')
    ->where('id', $request->movments_id)
    ->first();

if (!$movment) {
    return response()->json(['error' => 'Mouvement non trouvé ou n\'appartient pas à cet hôpital'], 404);
}

$record = DB::table('service_movments')
  ->where("services_id", $request->services_id)
  ->where("movments_id", $request->movments_id)
  ->first();

if($record ){ $data = $record ; }

 return response()->json([
  'success' => true,
  'data' =>  $data,
  'message' => 'Mouvement encours dans le service !'
]);

}



public function recordConsultation(Request $request)
{

  $request->validate([
   'reason' => 'required',
   'movments_id' => 'required|numeric',
   'services_id' => 'required|numeric'

 ]);

  // Vérifier que le mouvement existe
  $movment = DB::table('movments')
      ->where('id', $request->movments_id)
      ->first();

  if (!$movment) {
      return response()->json(['error' => 'Mouvement non trouvé ou n\'appartient pas à cet hôpital'], 404);
  }

  $existServiceMovment = DB::table('service_movments')
  ->where("services_id", $request->services_id)
  ->where("movments_id", $request->movments_id)
  ->first();


  if($existServiceMovment){
    DB::table('service_movments')
    ->where("services_id", $request->services_id)
    ->where("movments_id", $request->movments_id)
    ->update([
      'complaint'=>  $request->complaint,
      'reason'=>  $request->reason,
      'exam'=>  $request->exam,
      'observation'=> $request->observation,
      'summary'=> $request->summary,
      'operator'=> getPatientIdByUuid(trim($request->operator))
    ]);

    return response()->json([
      'code' =>302,
      'success' => true,
      'data' => $existServiceMovment,
      'message' => 'Mouvement du service actif modifie !'
    ]);

  }else{
   // L'isolation est gérée par la base de données.
   $serviceMovment =  DB::table('service_movments')->insert([
    'complaint'=>  $request->complaint,
    'exam'=>  $request->exam,
    'observation'=> $request->observation,
    'reason'=>  $request->reason,
    'summary'=> $request->summary,
    'operator'=> getPatientIdByUuid(trim($request->operator)),
    'services_id'=>  $request->services_id,
    'movments_id'=> $request->movments_id
  ]);


   return response()->json([
    'code' =>200,
    'success' => true,
    'data' => $serviceMovment,
    'message' => 'Mouvement du service actif ajoute !'
  ]);

 }
}


public function switchServices(Request $request)
{
 $request->validate([
   'movments_id' => 'required|numeric',
   'selectedService_id' => 'required|numeric'
 ]);

 $movment = Movment::find($request->movments_id);
 $movment->active_services_id = $request->selectedService_id;
 $movment->save();

 return response()->json([
  'success' => true,
  'data' =>$movment,
  'message' => 'Mouvement affecté au un autre service !'
]);

}

public function checkGetout(Request $request)
{
  $request->validate([
   'movments_id' => 'required|numeric'
 ]);

  // Récupérer l'ID de l'hôpital courant pour l'isolation multi-tenant
  // Vérifier que le mouvement existe
  $movment = DB::table('movments')
      ->where('id', $request->movments_id)
      ->first();

  if (!$movment) {
      return response()->json(['error' => 'Mouvement non trouvé'], 404);
  }

  $paid = DB::table('patient_movement_details')
  ->where('movments_id',$request->movments_id)
  ->where('paid',1)->first();

  if($paid){
    return response()->json([
      'success' => true,
      'data' =>1,
      'message' => ' Treaitment effectuté sur le patient !'
    ]);

  }else{

    return response()->json([
      'success' => true,
      'data' =>0,
      'message' => 'Aucun traitement effectué !'
    ]);
  }
}


public function getPatientMedicalsRecords(Request $request){

   $patient = Patient::where('uuid', $request->patient_uuid)->first();
   return response()->json([
      'success' => true,
      'data' => $patient,
      'message' => 'Aucun traitement effectué !'
    ]);

}




}



