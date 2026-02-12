<?php

namespace Modules\Cash\Http\Controllers\Api\V1;

use Modules\Acl\Entities\User;
use Modules\Cash\Http\Controllers\CashController;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cash\Entities\CashRegisterTransfert;

class CashRegisterTransfertController extends CashController
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $cashRegisterTransfert = CashRegisterTransfert::
        join('users as cashier', 'cash_register_transferts.cashier_id', '=', 'cashier.id')
        ->join('users as approver', 'cash_register_transferts.approver_id', '=', 'approver.id')
        ->join('cash_registers', 'cash_register_transferts.cash_registers_id', '=', 'cash_registers.id')
        ->select('cash_register_transferts.*', 'cash_register_transferts.statut', 'cashier.name as cashier_name', 'cashier.prenom as cashier_prenom', 'approver.name as approver_name', 'approver.prenom as approver_prenom', 'cash_registers.designation')
        ->orderBy('cash_register_transferts.created_at', 'desc')
        ->get();
    
        return response()->json([
            'message' => 'Historique récupérée avec succès',
            'data' => $cashRegisterTransfert
        ], 201);


    }

    
    

    public function getHistoriqueOpen()
    {
        $historicalOpen = CashRegisterTransfert::where('cash_register_transferts.statut', 0)
            ->join('users', 'cash_register_transferts.cashier_id', '=', 'users.id')
            ->join('cash_registers', 'cash_register_transferts.cash_registers_id', '=', 'cash_registers.id')
            ->select('cash_register_transferts.*', 'users.name', 'users.prenom', 'cash_registers.designation')
            ->orderBy('cash_register_transferts.created_at', 'desc')
            ->get();
    
        return response()->json([
            'message' => 'Historique récupérée avec succès',
            'data' => $historicalOpen
        ], 201);
    }

    public function getHistoriqueClose()
    {
        $historicalOpen = CashRegisterTransfert::where('cash_register_transferts.statut', 1)
            ->join('users', 'cash_register_transferts.cashier_id', '=', 'users.id')
            ->join('cash_registers', 'cash_register_transferts.cash_registers_id', '=', 'cash_registers.id')
            ->select('cash_register_transferts.*', 'users.name', 'users.prenom', 'cash_registers.designation')
            ->orderBy('cash_register_transferts.created_at', 'desc')
            ->get();
    
        return response()->json([
            'message' => 'Historique récupérée avec succès',
            'data' => $historicalOpen
        ], 201);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        // Validez les données de la requête
        $validatedData = $request->validate([
            'user_id' => 'required',
            'cashier_id' => 'required',
            'cash_registers_id' => 'required',
            'approver_id' => 'required',
            'fonds' => 'required',
            'solde' => 'required',
            'credits' => 'required',
            'statut' => 'required',
            'deleted_at' => 'nullable',
            'is_synced' => 'required',
        ]);
    
        // Créez un nouvel enregistrement CashRegisterTransfert
        $cashRegisterTransfert = CashRegisterTransfert::create($validatedData);
    
        return response()->json([
            'success' => true,
            'message' => 'Enregistrement créé avec succès.',
            'data' => $cashRegisterTransfert,
        ], 201);
    }
    

    public function getApprover()
    {
        $caissiers = User::All();

        return response()->json([
            'message' => 'Liste des caissieres principales',
            'data' => $caissiers
        ], 200);
    }
    




    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('CashRegisterTransfert::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('CashRegisterTransfert::edit');
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
