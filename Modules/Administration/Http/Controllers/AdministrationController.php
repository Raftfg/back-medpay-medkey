<?php

namespace Modules\Administration\Http\Controllers;

use App\Http\Controllers\Api\V1\ApiController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Modules\Administration\Entities\Department;
use Modules\Administration\Entities\Departement;
use Modules\Administration\Entities\Service;
use Modules\Administration\Entities\MedicalAct;
use Modules\Administration\Entities\TypeMedicalActs;

use Illuminate\Support\Str;

class AdministrationController extends ApiController
{
 public function __construct()
 {
  parent::__construct();
  $this->moduleAlias = strtolower(config('administration.name'));
  $this->mediaCollectionName = config("{$this->moduleAlias}.media_collection_name");
  $this->mediaDisk = config("{$this->moduleAlias}.media_disk");
}

/**return all departements*/
public function getDepartments()
{
  return response()->json([
    'success' => true,
    'data' => Department::all(),
    'message' => 'Liste des departments.'
  ]);
}


/**return all services*/
public function getServices()
{
  return response()->json([
    'success' => true,
    'data' => Service::all(),
    'message' => 'Liste des services'
  ]);
}


/** store services */
public function storeServices(Request $request)
{

  $service =   Service::create([
    'name' => $request->name,
    'description' => $request->description,
    'departments_id' => $request->departments_id,
    'code' => $request->code
  ]);

  return response()->json([
    'success' => true,
    'data' => $service->name,
    'message' => 'Liste des services'
  ]);
}


public function deleteServices(Request $request)
{
  Service::find($request->id)->delete();
  return response()->json([
    'success' => true,
    'data' => $request->id,
    'message' => 'Service suprimé'
  ]);
}

/**return  services by departments*/
public function getServicesByDepartment(Request $request)
{
  return response()->json([
    'success' => true,
    'data' => Service::where('departments_id', $request->department_id)->get(),
    'message' => 'Liste des services par département.'
  ]);
}

/**return  medicales actes by servivces */
public function getActesByServices(Request $request)
{
  return response()->json([
    'success' => true,
    'data' => MedicalAct::where('services_id', $request->services_id)->get(),
    'message' => 'Liste des actes medicaux par services.'
  ]);
}

public function getTypeMedicalActs(Request $request)
{
  return response()->json([
    'success' => true,
    'data' => TypeMedicalActs::all(),
    'message' => 'Liste des types actes medicaux.'
  ]);
}


public function storeActes(Request $request)
{
  $acte =   MedicalAct::create([
    'uuid' => Str::uuid(),
    'code' => $request->code,
    'designation' => $request->designation,
    'description' => $request->designation,
    'price' => $request->price,
    'type_medical_acts_id' => $request->type_medical_acts_id,
    'services_id' => $request->services_id

  ]);

  return response()->json([
    'success' => true,
    'data' => $acte,
    'message' => 'Liste des services'
  ]);
}

public function deleteActes(Request $request)
{
  MedicalAct::find($request->id)->delete();
  return response()->json([
    'success' => true,
    'data' => $request->id,
    'message' => 'Acte suprimé'
  ]);
}

public function getLastActeCode(Request $request)
{
  $serviceCode = Service::find($request->services_id)->code;
  $MedicalAct = MedicalAct::latest()->first();
  if($MedicalAct){
    $id = $MedicalAct->id+1;
  }else{
     $id = 1;
  }

  $code = strtoupper(substr($serviceCode,0,3).''.$id);
  return response()->json([
    'success' => true,
    'data' =>  $code,
    'message' => 'Acte suprimé'
  ]);
}

}
