<?php

namespace Modules\Cash\Http\Controllers\Api\V1;


use Illuminate\Http\Request;


use Modules\Acl\Entities\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Cash\Entities\AllocateCash;
use Modules\Cash\Entities\CashRegister;
use Illuminate\Contracts\Support\Renderable;

use Modules\Cash\Entities\HistoricalOpenClose;
use Modules\Cash\Http\Controllers\CashController;
use Illuminate\Support\Facades\Validator;
use Modules\Payment\Entities\Facture;
use Illuminate\Validation\Rule;

class AllocateCashController extends CashController
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $allocateCashiers = AllocateCash::all();

        foreach ($allocateCashiers as $allocateCashier) {
            # code...
            $isUsedInInvoices = Facture::where('cash_registers_id', $allocateCashier->cash_registers_id)
                ->where('user_id', $allocateCashier->cashier_id)
                ->exists();
        }

        return response()->json([
            'message' => ' Liste des caissiers affectés',
            'data' => $allocateCashiers

        ], 201);
    }

    public function getCashiersCashRegister($cashRegisterID)
    {
        $caissiers = AllocateCash::where('cash_registers_id', $cashRegisterID)->get();

        return response()->json([
            'message' => 'Liste des caissiers pour une caisse donnée',
            'data' => $caissiers
        ], 200);
    }

    public function getCashRegisterCashier($cashierID) // liste des caisses lié au caissier
    {
        // $caisses = AllocateCash::where('cashier_id', $cashierID)->get();
        $caisses = AllocateCash::where('cashier_id', $cashierID)
            ->join('cash_registers', 'allocate_cashes.cash_registers_id', '=', 'cash_registers.id')
            ->select('allocate_cashes.*', 'cash_registers.designation')
            ->get();

        return response()->json([
            'message' => 'Liste des caisses lié à un caissier donnée',
            'data' => $caisses

        ], 200);
    }

    public function getCashRegisterCashierCurrent($cashierID)
    {
        $caisseCurrent = AllocateCash::join('cash_registers', 'allocate_cashes.cash_registers_id', '=', 'cash_registers.id')
            ->where('allocate_cashes.cashier_id', $cashierID)
            ->where('allocate_cashes.is_choose', 1)
            ->select('cash_registers.*', 'allocate_cashes.cashier_id', 'allocate_cashes.is_choose', 'allocate_cashes.cash_registers_id', 'allocate_cashes.statut as allocateCashierStatut')
            ->first();

        return response()->json([
            'message' => ': récuperer la caisse actuel de l\'utilisateur connecté ',
            'data' => $caisseCurrent
        ], 200);
    }

    public function getHistoricalCurrent($cashRegisterID)
    {
        $latestHistoryCash = HistoricalOpenClose::where('cash_registers_id', $cashRegisterID)
            ->with('cashier') // Eager load the cashier relationship
            ->latest()
            ->first();

        return response()->json([
            'message' => 'Historique actuelle de la caisse',
            'data' => $latestHistoryCash
        ], 200);
    }

    public function chooseCashRegister($cashRegisterID, $cashierID)
    {
        // Vérifier si l'utilisateur est déjà affecté à la caisse

        $existingAllocation = AllocateCash::where('cashier_id', $cashierID)
            ->where('cash_registers_id', $cashRegisterID)
            ->first();
      
        if (!$existingAllocation) {
            // Retourner une erreur si l'utilisateur n'est pas déjà affecté à cette caisse
            return response()->json([
                'error' => 'L\'utilisateur n\'est pas assigné à cette caisse.',
            ], 422);
        }

        if ($existingAllocation->statut == 1) {
            return response()->json([
                'error' => 'Votre statut est inactif.',
            ], 422);
        }

        // Désélectionner toutes les caisses de ce caissier
        AllocateCash::where('cashier_id', $cashierID)->update(['is_choose' => 0]);

        // Sélectionner la caisse spécifique (cashRegisterID) pour l'utilisateur (cashierID)

        AllocateCash::where('cashier_id', $cashierID)
            ->where('cash_registers_id', $cashRegisterID)
            ->update(['is_choose' => 1]);

        // Sélectionner la caisse choisie par l'utilisateur
        $chosenCashRegister = AllocateCash::where('cashier_id', $cashierID)
            ->where('cash_registers_id', $cashRegisterID)
            ->join('cash_registers', 'allocate_cashes.cash_registers_id', '=', 'cash_registers.id')
            ->select('allocate_cashes.*', 'cash_registers.designation')
            ->get();

        return response()->json([
            'message' => 'Mise à jour de la caisse choisie par l\'utilisateur',
            'data' => $chosenCashRegister,
        ], 200);
        
    }


    public function getUserByID($user_id)
    {
        $users = User::where('id', $user_id)->get();

        return response()->json([
            'message' => 'Liste des users',
            'data' => $users

        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('AllocateCash::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
        $request->validate(
            [
                'user_id' => 'required|exists:users,id',
                'cashier_id' => 'required|exists:users,id',
                'selected_date' => 'required|date',
                'opening_time' => 'required|date_format:H:i',
                'closing_time' => 'required|date_format:H:i|after:opening_time',
                'cash_registers_id' => 'required|exists:cash_registers,id',
                'statut' => 'required|boolean',
            ],

            [
                'cashier_id.required' => 'Ce champs est obligatoire.',
                'cash_registers_id.required' => 'Ce champs est obligatoire.',
                'selected_date.required' => 'Ce champs est obligatoire.',
                'opening_time.required' => 'Ce champs est obligatoire.',
                'closing_time.required' => 'Ce champs est obligatoire',
                'closing_time.after' => 'L\'heure de fermeture doit être postérieure à l\'heure d\'ouverture.',
                'statut.required' => 'Ce champs est obligatoire.',
            ]
        );


        $existingAllocation = AllocateCash::where('cashier_id', $request->input('cashier_id'))
            ->where('cash_registers_id', $request->input('cash_registers_id'))
            ->where('selected_date', $request->input('selected_date'))
            ->where(function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('opening_time', '>=', $request->input('opening_time'))
                        ->where('opening_time', '<=', $request->input('closing_time'));
                })->orWhere(function ($query) use ($request) {
                    $query->where('closing_time', '>=', $request->input('opening_time'))
                        ->where('closing_time', '<=', $request->input('closing_time'));
                });
            })
            ->first();


        if ($existingAllocation) {
            return response()->json([
                'message' => 'Cette affectation du caissier existe déjà.'
            ], 422); // Statut HTTP 422 - Unprocessable Entity
        }
        // Créer une nouvelle allocation de caisse

        $allocation = AllocateCash::create([
            // 'user_id' => $request->input('user_id'),
            // 'user_id' => 1,
            // 'user_id' => Auth::id(),
            'user_id' => auth()->user()->id,
            'cashier_id' => $request->input('cashier_id'),
            'cash_registers_id' => $request->input('cash_registers_id'),
            'selected_date' => $request->input('selected_date'),
            'opening_time' => $request->input('opening_time'),
            'closing_time' => $request->input('closing_time'),
            'statut' => $request->input('statut', 0), // Default to 0 if not provided
            'is_choose' => $request->input('statut', 0), // Default to 0 if not provided
            // L'UUID sera automatiquement généré en raison de la contrainte d'unicité dans la base de données
        ]);


        return response()->json([
            'data' => $allocation,
            'message' => 'Allocation de caisse créée avec succès'

        ], 201);
    }


    public function update(Request $request,  $uuid)
    {
        //

        $validator = Validator::make(

            $request->all(),
            [
                'user_id' => 'required|exists:users,id',
                'cashier_id' => 'required|exists:users,id',
                'selected_date' => 'required|date',
                'opening_time' => 'required|date_format:H:i',
                'closing_time' => 'required|date_format:H:i|after:opening_time',
                'cash_registers_id' => 'required|exists:cash_registers,id',
                'statut' => 'required|boolean',
            ],

            [
                'cashier_id.required' => 'Ce champs est obligatoire.',
                'cash_registers_id.required' => 'Ce champs est obligatoire.',
                'selected_date.required' => 'Ce champs est obligatoire.',
                'opening_time.required' => 'Ce champs est obligatoire.',
                'closing_time.required' => 'Ce champs est obligatoire',
                'closing_time.after' => 'L\'heure de fermeture doit être postérieure à l\'heure d\'ouverture.',
                'statut.required' => 'Ce champs est obligatoire.',
            ]
        );


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $allocateCash = AllocateCash::where('uuid', $uuid)->first();

        if (!$allocateCash) {
            return response()->json(['message' => 'Cet enragistrement dans la table n\'existe pas.'], 404);
        }

        $isUsedInInvoices = Facture::where('cash_registers_id', $allocateCash->cash_registers_id)
            ->where('user_id', $allocateCash->cashier_id)
            ->exists();

        // if ($isUsedInInvoices) {
        //     return response()->json(['message' => 'Impossible de modifier car l\'utilisateur a déjà effctué un payement à cette caisse '], 422);
        // }

        $existingAllocation = AllocateCash::where('cashier_id', $request->input('cashier_id'))
            ->where('cash_registers_id', $request->input('cash_registers_id'))
            ->where('selected_date', $request->input('selected_date'))
            ->where('uuid', '!=', $uuid) // Exclure l'enregistrement actuel
            ->where(function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('opening_time', '>=', $request->input('opening_time'))
                        ->where('opening_time', '<=', $request->input('closing_time'));
                })->orWhere(function ($query) use ($request) {
                    $query->where('closing_time', '>=', $request->input('opening_time'))
                        ->where('closing_time', '<=', $request->input('closing_time'));
                });
            })
            ->first();

        if ($existingAllocation && $existingAllocation->uuid !== $uuid) {
            return response()->json([
                'message' => 'Cette affectation du caissier existe déjà.'
            ], 422);
        }
        // Créer une nouvelle allocation de caisse

        $allocateCash->update([
            'user_id' => auth()->user()->id,
            'cashier_id' => $request->input('cashier_id'),
            'cash_registers_id' => $request->input('cash_registers_id'),
            'selected_date' => $request->input('selected_date'),
            'opening_time' => $request->input('opening_time'),
            'closing_time' => $request->input('closing_time'),
            'statut' => $request->input('statut'),
        ]);

        return response()->json([
            'data' => $allocateCash,
            'isUsedInInvoices' => $isUsedInInvoices,
            'message' => 'Mise à jour avec succès'
        ], 200);
    }






    public function delete($uuid)
    {
        $allocateCash = AllocateCash::where('uuid', $uuid)->first();

        if (!$allocateCash) {
            return response()->json(['message' => 'Cet enregistrement dans la table n\'existe pas.'], 404);
        }

        $isUsedInInvoices = Facture::where('cash_registers_id', $allocateCash->cash_registers_id)
            ->where('user_id', $allocateCash->cashier_id)
            ->exists();

        if ($isUsedInInvoices) {
            return response()->json(['message' => 'Impossible de supprimer car l\'utilisateur a déjà effectué un paiement à cette caisse.'], 422);
        }

        $allocateCash->delete();

        return response()->json(['message' => 'Enregistrement supprimé avec succès.'], 200);
    }





    public function getCahiers()
    {
        $users = DB::table('users')
            ->get();
        return response()->json([
            'data' => $users,
        ], 200);
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('AllocateCash::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('AllocateCash::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */


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
