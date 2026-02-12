<?php

namespace Modules\Recouvrement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Payment\Entities\Facture;
use Modules\Cash\Entities\CashRegister;
use Modules\Recouvrement\Entities\Recouvre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class RecouvrementController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function index()
     {
         // Récupérer tous les recouvrements depuis la base de données, triés par date de création décroissante
         $recouvrements = Recouvre::orderBy('created_at', 'DESC')->get();
     
         // Retourner les recouvrements au format JSON
         return response()->json(['data' => $recouvrements]);
     }
     
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('recouvrement::create');
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request, $reference)
    {
        // $validator = Validator::make($request->all(), [
        //     'partial_amount' => 'required', // Assurez-vous que partial_amount est requis et numérique
        //     'mode_payements_id' => 'required',
        // ], [
        //     'partial_amount.required' => "Ce champs est obligatoire.",
        //     'mode_payements_id.required' => "Ce champs est obligatoire.",
        // ]);

        $validator = Validator::make(
            $request->all(),
            [
                'partial_amount' => [
                    'required',
                    'numeric',
                    'regex:/^[0-9]+(?:\.[0-9]{1,2})?$/',
                    function ($attribute, $value, $fail) {
                        if (strpos($value, ',') !== false) {
                            $fail('Le champ ' . $attribute . ' doit utiliser un point (.) comme séparateur décimal.');
                        }
                    },
                    'gt:0',
                ],
                'mode_payements_id' => 'required',
            ],

            [
                'partial_amount.required' => 'Ce champ est obligatoire.',
                'partial_amount.numeric' => 'Ce champ doit être un nombre avec au plus deux chiffres après le point.',
                'partial_amount.regex' => 'Ce champ doit être un nombre avec au plus deux chiffres après le point.',
                'partial_amount.gt' => 'Le montant doit être supérieur à 0.',
                'mode_payements_id.required' => 'ce champ est obligatoire.',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $partialAmount = $request->input('partial_amount');
        $modePayementsId = $request->input('mode_payements_id');

        $current_user = auth()->user()->id;

        $firstFacture = Facture::where('reference', $reference)->first();

        if (!$firstFacture) {
            return response()->json(['error' => 'Facture non trouvée'], 404);
        }

        $cashRegister = CashRegister::find($firstFacture->cash_registers_id);

        if (!$cashRegister || !is_numeric($firstFacture->cash_registers_id)) {
            return response()->json(['error' => 'Caisse non trouvée'], 404);
        }

        if ($partialAmount != 0.00 && $partialAmount != null) {

            $cashRegister->total_partial += $partialAmount;

            switch ($modePayementsId) {
                case 1:
                    $cashRegister->total_espece += $partialAmount;

                    break;
                case 2:
                    $cashRegister->totalMtnMomo += $partialAmount;
                    break;
                case 3:
                    $cashRegister->totalMoovMomo += $partialAmount;
                    break;
                case 4:
                    $cashRegister->totalCeltis += $partialAmount;
                    break;
                case 5:
                    $cashRegister->totalCarteBancaire += $partialAmount;
                    break;
                case 6:
                    $cashRegister->totalCarteCredit += $partialAmount;
                    break;
                case 7:
                    $cashRegister->totalTresorPay += $partialAmount;
                    break;
                case 8:
                    $cashRegister->credits += $partialAmount;
                    break;
                default:
                    // Gestion des cas non couverts
                    break;
            }
        }

        $cashRegister->save();

        $montant_brut = Facture::where('reference', $reference)->sum('amount');
        $updateData = [];


        // Mise à jour de la colonne partial_amount si elle est fournie

        if ($partialAmount != 0 || $partialAmount != 0.00) {
            // $updateData['partial_amount'] = isset($updateData['partial_amount']) ? $updateData['partial_amount'] + $partialAmount : $partialAmount;
            $updateData['partial_amount'] = $firstFacture->partial_amount + $partialAmount;
        }


        Facture::where('reference', $reference)->update($updateData);

        // Récupérez la facture mise à jour
        $updatedFacture = Facture::where('reference', $reference)->first();

        // Vérifiez si le montant brut est égal au montant partiel mis à jour
        if ($updatedFacture->partial_amount == $montant_brut) {
            // Mise à jour de la colonne paid
            Facture::where('reference', $reference)->update(['paid' => 1]);
        }

        $movmentId = optional($firstFacture)->movments_id;

        if ($movmentId !== null) {
            // Récupérez les informations du patient pour le movments_id
            $patientInfoResponse = $this->getPatientInfo($movmentId);

            if ($patientInfoResponse->getStatusCode() == 200) {
                $patientInfo = json_decode($patientInfoResponse->getContent())->data;
            } else {
                return response()->json(['error' => 'Erreur lors de la récupération des informations du patient'], 500);
            }
        }

        $recouvrement = new Recouvre();
        $recouvrement->user_id = $current_user; // Remplacez par l'ID de l'utilisateur approprié
        $recouvrement->is_synced = 0;
        $recouvrement->reference_facture = $reference;
        $recouvrement->type = $firstFacture->type;
        $recouvrement->movement_id = $movmentId; // Remplacez par l'ID du mouvement approprié
        $recouvrement->mode_payements_id = $modePayementsId; // Remplacez par l'ID du mouvement approprié
        $recouvrement->montant_facture = $montant_brut; // Remplacez par le montant approprié
        // $recouvrement->montant_paye = $partialAmount + $firstFacture->partial_amount; // Remplacez par le montant approprié
        $recouvrement->pourcentage_assurance =   $firstFacture->percentageassurance; // Remplacez par le pourcentage approprié
        $recouvrement->montant_saisi = $partialAmount; // Remplacez par le montant approprié
        $recouvrement->date_recouvrement = now(); // Remplacez par la date appropriée
        $recouvrement->save();

        return response()->json([
            'success' => true,
            'patientInfo' => $patientInfo,
            'message' => 'remboursement payée avec succès.',
        ], 200);
    }

    public function getPatientInfo($searchParam)
    {
        $result = DB::table('movments')
            ->join('patients', 'movments.patients_id', '=', 'patients.id')
            ->select('patients.id', 'patients.lastname', 'patients.firstname', 'patients.emergency_contac', 'patients.phone', 'patients.maison', 'patients.ipp', 'patients.gender', 'patients.email', 'movments.iep as iep')
            ->where('movments.id', $searchParam)
            ->first(); // Utilisez first() pour obtenir le premier résultat

        if (!$result) {
            return response()->json(['message' => 'Patient introuvable'], 404);
        }

        return response()->json(['data' => $result]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('recouvrement::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('recouvrement::edit');
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
