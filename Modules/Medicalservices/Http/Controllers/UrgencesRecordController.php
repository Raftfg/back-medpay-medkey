<?php

namespace Modules\Medicalservices\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Modules\Medicalservices\Entities\UrgencesRecord;
use Modules\Patient\Entities\Patiente;


class UrgencesRecordController extends Controller
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
         'category' => 'required',
         'level' => 'required',
         'operator' => 'required',
         'description' => 'required',
         'emergency_actions' => 'required',
         'parent' => 'required',
         'movments_id' => 'required|numeric',
         'services_id' => 'required|numeric'
     ]);

        $odlRecord = UrgencesRecord::where('services_id', $request->services_id)
        ->where('movments_id', $request->movments_id)->first();

        if($odlRecord){
            $odlRecord->update([
               'category' => $request->category,
               'level' => $request->level,
               'description' => $request->description,
               'emergency_actions' => $request->emergency_actions,
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

           $record = UrgencesRecord::create([
              'uuid'=> Str::uuid(),
              'services_id' => $request->services_id,
              'movments_id' => $request->movments_id,
              'category' => $request->category,
              'level' => $request->level,
              'description' => $request->description,
              'emergency_actions' => $request->emergency_actions,
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
        'category'=> "",
        'level'=>"",
        'description'=>"",
        'emergency_actions'=> "",
        'parent'=> "",
        'summary'=>"",
        'operator'=>"",
        'services_id'=> "",
        'movments_id'=> ""
    ];

    $record = UrgencesRecord::where('services_id', $request->services_id)
    ->where('movments_id', $request->movments_id)->first();


    if($record ){ $data = $record ; }

    return response()->json([
        'success' => true,
        'data' =>  $data,
        'message' => 'Urgence Record.'
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



 public function getUrgencesGravities( Request $request)
   {
       return response()->json([
        'success' => true,
        'data' => urgencesGravites($request->code) ,
        'message' => 'Liste des gravite par categorie des urgnces'
    ]);
   }

 public function getUrgencesCategories( Request $request)
    {
       return response()->json([
        'success' => true,
        'data' => urgencesCategories(),
        'message' => 'Liste des categories des urgnces'
    ]);
   }

}
