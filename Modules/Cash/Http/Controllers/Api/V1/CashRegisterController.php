<?php

namespace Modules\Cash\Http\Controllers\Api\V1;

use Modules\Payment\Entities\Facture;
use Modules\Cash\Http\Controllers\CashController;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cash\Entities\CashRegister;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;



class CashRegisterController extends CashController
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function index()
    {
        $cashRegister = CashRegister::orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => ' caisse créée avec succès',
            'data' => $cashRegister

        ], 201);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('CashRegister::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

        $request->validate([

            'designation' => 'required|string|regex:/^[A-Za-z]{2}[A-Za-z0-9\s]*$/|unique:cash_registers|max:50',
            'description' => 'required|string|regex:/^[A-Za-z]{2}[A-Za-z0-9\s]*$/|unique:cash_registers|max:80',
            'type' => 'required|string',
            'statut' => 'boolean',
            'is_synced' => 'boolean',
        ]);

        $CashRegister = new CashRegister();
        // $CashRegister->user_id = 1;
        $CashRegister->user_id = auth()->id();
        $CashRegister->designation = $request->designation;
        $CashRegister->description = $request->description;
        $CashRegister->type = $request->type;
        $CashRegister->total_partial = $request->total_partial ?? 0;
        $CashRegister->solde = $request->solde ?? 0;
        $CashRegister->credits = $request->credits ?? 0;
        $CashRegister->total_espece = $request->total_espece ?? 0;
        $CashRegister->totalMtnMomo = $request->totalMtnMomo ?? 0;
        $CashRegister->totalMoovMomo = $request->totalMoovMomo ?? 0;
        $CashRegister->totalCeltis    = $request->totalCeltis ?? 0;
        $CashRegister->totalCarteCredit = $request->totalCarteCredit ?? 0;
        $CashRegister->totalCarteBancaire = $request->totalCarteBancaire ?? 0;
        $CashRegister->totalTresorPay = $request->totalTresorPay ?? 0;
        $CashRegister->statut = $request->statut ?? 1;
        $CashRegister->is_synced = $request->is_synced ?? 0;

        $CashRegister->save();

        return response()->json([
            'message' => ' caisse créée avec succès',
            'data' => $CashRegister

        ], 201);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */

    public function update(Request $request, $uuid)
    {

        $validator = Validator::make($request->all(), [
            'designation' => [
                'required',
                'string',
                'regex:/^[\p{L}A-Za-z0-9\s]{2,}$/u',
                Rule::unique('cash_registers', 'designation')->ignore($uuid, 'uuid'),
                'max:100',
            ],
            'description' => [
                'required',
                'string',
                'regex:/^[\p{L}A-Za-z0-9\s]{2,}$/u',
                Rule::unique('cash_registers', 'description')->ignore($uuid, 'uuid'),
                'max:120',
            ],
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $cashRegister = CashRegister::where('uuid', $uuid)->first();

        if (!$cashRegister) {
            return response()->json(['message' => 'Caisse non trouvée.'], 404);
        }

        // Vérifier si l'ID de la caisse ne se trouve pas dans la table des factures
        $isUsedInInvoices = Facture::where('cash_registers_id', $cashRegister->id)->exists();

        if ($isUsedInInvoices) {
            return response()->json(['message' => 'Impossible de modifier la caisse car elle est déjà utilisée pour un payement'], 422);
        }

        // Mettre à jour les champs nécessaires
        $cashRegister->designation = $request->designation;
        $cashRegister->description = $request->description;

        $cashRegister->save();

        return response()->json([
            'message' => 'Caisse modifiée avec succès',
            'data' => $cashRegister,
        ], 200);
    }


    public function addFund($uuid, $amount)
    {
        // Trouver l'enregistrement de la caisse par uuid
        $cashRegister = CashRegister::firstWhere('uuid', $uuid);

        if (!$cashRegister) {
            return response()->json(['message' => 'La caisse n\'a pas été trouvée'], 404);
        }

        // $currentFunds = $cashRegister->fonds;
        // $newFunds = $currentFunds + $amount;
        $newFunds =  $amount;

        // Mettez à jour la colonne "fonds" avec la nouvelle valeur
        $cashRegister->update(['fonds' => $amount]);

        return response()->json([
            'message' => 'Fonds ajoutés avec succès', 'new_funds',
            'data' => $newFunds

        ], 201);
    }

    public function rapport()
    {
        $nbre = CashRegister::count();

        // Pour récupérer le total de fonds
        $totalFonds = CashRegister::sum('fonds');

        // Pour récupérer le total de solde
        $totalSolde = CashRegister::sum('solde');

        // Pour récupérer le total des crédits
        $credit = CashRegister::sum('credits');

        return response()->json([
            'message' => 'rapport ',
            'data' => [
                'nbre' => $nbre,
                'totalcash' => $totalFonds + $totalSolde,
                'credit' => $credit,
            ]
        ], 201);
    }

    public function bordereauTransfert($idCashRegister)
    {
        $caisseName = CashRegister::select('designation')
            ->where('cash_registers_id', $idCashRegister)->get();

        $totalAEncaisser = Facture::whereNotNull('partial_amount')
            ->where('mode_payements_id', '<>', 8)
            ->whereRaw('partial_amount < amount')
            ->where('cash_registers_id', $idCashRegister)

            ->sum(DB::raw('amount - partial_amount'));

        $totalMontantRecu = Facture::where('mode_payements_id', '<>', 8)
            ->where('cash_registers_id', $idCashRegister)
            ->where(function ($query) {
                $query->whereNull('partial_amount')
                    ->orWhereRaw('partial_amount < amount');
            })
            ->sum(DB::raw('IFNULL(partial_amount, amount)'));

        $montantsACredit = Facture::where('cash_registers_id', $idCashRegister)
            ->where('mode_payements_id', 8)
            ->sum(DB::raw('amount'));

        $totalEspece = Facture::where('mode_payements_id', 1)
            ->where('cash_registers_id', $idCashRegister)
            ->where(function ($query) {
                $query->whereNull('partial_amount')
                    ->orWhereRaw('partial_amount < amount');
            })
            ->sum(DB::raw('IFNULL(partial_amount, amount)'));

        $totalMtnMomo = Facture::where('mode_payements_id', 2)
            ->where('cash_registers_id', $idCashRegister)
            ->where(function ($query) {
                $query->whereNull('partial_amount')
                    ->orWhereRaw('partial_amount < amount');
            })
            ->sum(DB::raw('IFNULL(partial_amount, amount)'));

        $totalMoovMomo = Facture::where('mode_payements_id', 3)
            ->where('cash_registers_id', $idCashRegister)
            ->where(function ($query) {
                $query->whereNull('partial_amount')
                    ->orWhereRaw('partial_amount < amount');
            })
            ->sum(DB::raw('IFNULL(partial_amount, amount)'));

        $totalCeltis = Facture::where('mode_payements_id', 4)
            ->where('cash_registers_id', $idCashRegister)
            ->where(function ($query) {
                $query->whereNull('partial_amount')
                    ->orWhereRaw('partial_amount < amount');
            })
            ->sum(DB::raw('IFNULL(partial_amount, amount)'));

        $totalCarteBancaire = Facture::where('mode_payements_id', 5)
            ->where('cash_registers_id', $idCashRegister)
            ->where(function ($query) {
                $query->whereNull('partial_amount')
                    ->orWhereRaw('partial_amount < amount');
            })
            ->sum(DB::raw('IFNULL(partial_amount, amount)'));


        $totalCarteCredit = Facture::where('mode_payements_id', 6)
            ->where('cash_registers_id', $idCashRegister)
            ->where(function ($query) {
                $query->whereNull('partial_amount')
                    ->orWhereRaw('partial_amount < amount');
            })
            ->sum(DB::raw('IFNULL(partial_amount, amount)'));

        $totalTresorPay = Facture::where('mode_payements_id', 7)
            ->where('cash_registers_id', $idCashRegister)
            ->where(function ($query) {
                $query->whereNull('partial_amount')
                    ->orWhereRaw('partial_amount < amount');
            })
            ->sum(DB::raw('IFNULL(partial_amount, amount)'));


        return response()->json([
            'message' => 'rapport ',
            'data' => [
                'caisseName' => $caisseName,
                'totalAEncaisser' => $totalAEncaisser,
                'montantsACredit' => $montantsACredit,
                'totalMontantRecu' => $totalMontantRecu,
                'totalEspece' => $totalEspece,
                'totalMtnMomo' => $totalMtnMomo,
                'totalMoovMomo' => $totalMoovMomo,
                'totalCeltis' => $totalCeltis,
                'totalCarteBancaire' => $totalCarteBancaire,
                'totalCarteCredit' => $totalCarteCredit,
                'totalTresorPay' => $totalTresorPay,

            ]
        ], 201);
    }




    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('CashRegister::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('CashRegister::edit');
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
