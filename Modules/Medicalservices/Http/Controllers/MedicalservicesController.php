<?php

namespace Modules\Medicalservices\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Modules\Movment\Entities\Movment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


use Modules\Patient\Entities\Patiente;
use Modules\Administration\Entities\MedicalAct;
use Modules\Administration\Entities\Service;

class MedicalservicesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        //return view('medicalservices::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        //return view('medicalservices::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
       // return view('medicalservices::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        //return view('medicalservices::edit');
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


    /**return  medicales actes by servivces */
    public function getActesByServicesCode(Request $request)
    {
        $service = Service::find($request->service_id);
        return response()->json([
            'success' => true,
            'service' => $service,
            'data' => MedicalAct::where('services_id', $service->id)->get(),
            'message' => 'Liste des actes medicaux par services.'
        ]);
    }

     /**return  medicales actes by servivces */
    public function getServiceByCode(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => Service::where('code',$request->service_code)->first(),
            'message' => 'Liste des actes medicaux par services.'
        ]);
    }
}
