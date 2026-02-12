<?php

namespace Modules\Cash\Http\Controllers\Api\V1;

use Barryvdh\DomPDF\Facade\Pdf;


use Modules\Cash\Http\Controllers\CashController;
use Illuminate\Support\Facades\File;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cash\Entities\HistoricalOpenClose;

class HistoricalOpenCloseController extends CashController
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $HistoricalOpenClose = HistoricalOpenClose::all();

        return response()->json([
            'message' => 'Historique recupérée avec succès',
            'data' => $HistoricalOpenClose

        ], 201);
    }

    public function getHistoriqueOpen()
    {
        $historicalOpen = HistoricalOpenClose::where('historical_open_closes.statut', 0)
            ->join('users', 'historical_open_closes.cashier_id', '=', 'users.id')
            ->join('cash_registers', 'historical_open_closes.cash_registers_id', '=', 'cash_registers.id')
            ->select('historical_open_closes.*', 'users.name', 'users.prenom', 'cash_registers.designation')
            ->orderBy('historical_open_closes.created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Historique récupérée avec succès',
            'data' => $historicalOpen
        ], 201);
    }

    public function getHistoriqueClose()
    {
        $historicalOpen = HistoricalOpenClose::where('historical_open_closes.statut', 1)
            ->join('users', 'historical_open_closes.cashier_id', '=', 'users.id')
            ->join('cash_registers', 'historical_open_closes.cash_registers_id', '=', 'cash_registers.id')
            ->select('historical_open_closes.*', 'users.name', 'users.prenom', 'cash_registers.designation')
            ->orderBy('historical_open_closes.created_at', 'desc')
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
    public function create()
    {
        return view('HistoricalOpenClose::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'cashier_id' => 'required|exists:users,id',
            'cash_registers_id' => 'required|exists:cash_registers,id',
            'solde' => 'required|numeric',
            'credits' => 'required|numeric',
            'statut' => 'required|boolean',
        ]);

        // Créer un nouvel enregistrement HistoricalOpenClose

        $historicalOpenClose = HistoricalOpenClose::create([
            // 'user_id' => $request->input('user_id'),
            // 'user_id' => 1,
            'user_id' => auth()->user()->id,
            'cashier_id' => auth()->user()->id,
            'cash_registers_id' => $request->input('cash_registers_id'),
            'solde' => $request->input('solde'),
            'credits' => $request->input('credits'),
            'total_partial' => $request->input('total_partial'),
            'totalespece' => $request->input('totalespece'),
            'totalMtnMomo' => $request->input('totalMtnMomo'),
            'totalMoovMomo' => $request->input('totalMoovMomo'),
            'totalCeltis' => $request->input('totalCeltis'),
            'totalCarteBancaire' => $request->input('totalCarteBancaire'),
            'totalCarteCredit' => $request->input('totalCarteCredit'),
            'totalTresorPay' => $request->input('totalTresorPay'),
            'statut' => $request->input('statut'),
           
            // L'UUID sera automatiquement généré en raison de la contrainte d'unicité dans la base de données
        ]);


        // if ($historicalOpenClose->statut == 1) {

        //     $htmlContent = view('cash::pdf.bordereau-transfert', compact('historicalOpenClose'))->render();
        


        //     $pdf = PDF::loadHTML($htmlContent);

        //     $storagePath = public_path('storage/bordereau/pdf/');

        //     // Si le dossier n'existe pas, créez-le
        //     if (!File::exists($storagePath)) {
        //         File::makeDirectory($storagePath, 0777, true, true);
        //     } else {
        //         // Si le dossier existe, assurez-vous qu'il a les bonnes autorisations
        //         chmod($storagePath, 0777);

        //         // Si vous voulez appliquer les autorisations aux sous-répertoires récursivement
        //         $this->recursiveChmod($storagePath, 0777);
        //     }
        //     // Vous pouvez ajuster le nom du fichier PDF si nécessaire
        //     $filename = $storagePath . 'bordereau-transfert_' . $historicalOpenClose->id . '.pdf';
        //     // Enregistrez le PDF sur le serveur si nécessaire
        //     $pdf->save($filename);
        // } else {
        //     $filename = null; 
        // }
        // Retournez la réponse avec le PDF
        return response()->json([
            'message' => 'HistoricalOpenClose créé avec succès',
            'data' => $historicalOpenClose,
            // 'pdf_path' => $filename,
        ], 201);
    }







    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('HistoricalOpenClose::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('HistoricalOpenClose::edit');
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
