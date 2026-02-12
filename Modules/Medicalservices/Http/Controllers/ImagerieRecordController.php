<?php

namespace Modules\Medicalservices\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Modules\Medicalservices\Entities\ImagerieRecord;

class ImagerieRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        $data  =  ImagerieRecord::where('services_id',$request->services_id)
        ->where('movments_id', $request->movments_id)
        ->get(['act_code','reason','result','summary','operator']);

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
         'reason' => 'required',
         'result' => 'required',
         'summary' => 'required',
         'operator' => 'required'
     ]);

        DB::beginTransaction();

        $record = ImagerieRecord::create([
          'uuid'=> Str::uuid(),
          'services_id' => $request->services_id,
          'movments_id' => $request->movments_id,
          'act_code' => $request->act_code,
          'reason' => $request->reason,
          'result' => $request->result,
          'summary' => $request->summary,
          'operator' => getPatientIdByUuid(trim($request->operator))
      ]);

        if($request->picture){
           $record->pictures = base64_encode(file_get_contents($request->file('picture')->path()));
           $record->save();
       }
       DB::commit();

       return response()->json([
        'success' => true,
        'data' => $record,
        'message' => 'Liste des patients.'
    ]);


    /*if($request->file()) {
        $file_name = time().'_'.$request->file->getClientOriginalName();
        $file_path = $request->file('file')->storeAs('uploads', $file_name, 'public');

        $fileUpload->name = time().'_'.$request->file->getClientOriginalName();
        $fileUpload->path = '/storage/' . $file_path;
        $fileUpload->save();

        return response()->json(['success'=>'File uploaded successfully.']);
    }*/
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
