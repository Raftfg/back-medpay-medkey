<?php

namespace Modules\Medicalservices\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Modules\Medicalservices\Entities\MaterniteRecord;

class MaterniteRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('medicalservices::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('medicalservices::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
       $request->validate([
         'reason' => 'required',
         'complaint' => 'required',
         'operator' => 'required',
         'summary' => 'required',
         'actions' => 'required',
         'movments_id' => 'required|numeric',
         'services_id' => 'required|numeric'
     ]);

       $odlRecord = MaterniteRecord::where('services_id', $request->services_id)
       ->where('movments_id', $request->movments_id)->first();



       if($odlRecord){
        $odlRecord->update([
          'reason' => $request->reason,
          'complaint' => $request->complaint,
          'observation' => $request->observation,
          'actions' => $request->actions,
          'parent' => $request->parent,
          'summary' => $request->summary,
          'operator' => getPatientIdByUuid(trim($request->operator))
      ]);

        return response()->json([
            'success' => true,
            'data' => $odlRecord,
            'message' => 'Liste des patients.'
        ]);

    }else{

     $record = MaterniteRecord::create([
      'uuid'=> Str::uuid(),
      'services_id' => $request->services_id,
      'movments_id' => $request->movments_id,
      'reason' => $request->reason,
      'complaint' => $request->complaint,
      'observation' => $request->observation,
      'actions' => $request->actions,
      'parent' => $request->parent,
      'summary' => $request->summary,
      'operator' => getPatientIdByUuid(trim($request->operator))
  ]);

     return response()->json([
        'success' => true,
        'data' => $record,
        'message' => 'Liste des patients.'
    ]);

 }
}


public function getRecord(Request $request)
{
  $data = [
    'reason'=> "",
    'complaint'=>"",
    'observation'=>"",
    'actions'=> "",
    'parent'=> "",
    'operator'=>"",
    'services_id'=> "",
    'movments_id'=> ""
];

$record = MaterniteRecord::where('services_id', $request->services_id)
->where('movments_id', $request->movments_id)->first();


if($record ){ $data = $record ; }

return response()->json([
    'success' => true,
    'data' =>  $data,
    'message' => 'Infirmeie Record.'
]);

}


    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('medicalservices::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('medicalservices::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
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
}
