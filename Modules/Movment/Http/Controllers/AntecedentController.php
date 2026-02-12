<?php

namespace Modules\Movment\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;


use Illuminate\Support\Facades\DB;
use Modules\Movment\Entities\Antecedent;
use Carbon\Carbon;



class AntecedentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => Antecedent::where('patients_id',$request->patients_id)->get(),
            'message' => 'Liste des mensurations patienrs.'
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
         'name' => 'required',
         'type' => 'required',
         'movments_id' => 'required|numeric',
         'patients_id' => 'required|numeric',
     ]);

        DB::beginTransaction();

        $Antecedent = Antecedent::create([
            'uuid'=> Str::uuid(),
            'patients_id'=> $request->patients_id,
            'name'=> $request->name,
            'description'=> $request->description,
            'type' =>  $request->type,
            'movments_id' => $request->movments_id
        ]);
       DB::commit();

        return response()->json([
            'success' => true,
            'data' =>$Antecedent,
            'message' => 'Liste des mensurations patients.'
        ]);


    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('movment::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('movment::edit');
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
    public function destroy(Request $request)
    {
         $Antecedent = Antecedent::find($request->id)->delete();
         return response()->json([
            'success' => true,
            'data' =>$request->id,
            'message' => 'Supression bien effectué.'
        ]);
    }
}
