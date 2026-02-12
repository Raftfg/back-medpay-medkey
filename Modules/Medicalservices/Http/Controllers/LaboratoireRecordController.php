<?php

namespace Modules\Medicalservices\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Modules\Medicalservices\Entities\LaboratoireRecord;

class LaboratoireRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $data  =  LaboratoireRecord::where('services_id',$request->services_id)
        ->where('movments_id', $request->movments_id)
        ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Liste des opérations du patient.'
        ]);
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
           'services_id' => 'required',
           'movments_id' => 'required',
           'act_code' => 'required',
           'intrant' => 'required',
           'param' => 'required',
           'ref_value' => 'required',
           'value' => 'required',
           'unit' => 'required',
           'operator' => 'required'
       ]);

       DB::beginTransaction();
       $record = LaboratoireRecord::create([
          'uuid'=> Str::uuid(),
          'services_id' => $request->services_id,
          'movments_id' => $request->movments_id,
          'act_code' => $request->act_code,
          'intrant' => $request->intrant,
          'param' => $request->param,
          'ref_value' => $request->ref_value,
          'value' => $request->value,
          'unit' => $request->unit,
          'summary' => $request->summary,
          'operator' => getPatientIdByUuid(trim($request->operator))
      ]);

     DB::commit();
     return response()->json([
        'success' => true,
        'data' => $record,
        'message' => 'Opération de LAboratoire ajoutée.'
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
