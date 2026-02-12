<?php

namespace Modules\Remboursement\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Stock\Entities\Destock;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\Movment\Entities\Movment;
use Modules\Payment\Entities\Facture;
use Modules\Patient\Entities\Patiente;

class RemboursementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('remboursement::index');
    }
    public function listRemboursements()
    {
        $remboursements = DB::table('remboursements')
            ->join('remboursement_details', 'remboursements.id', '=', 'remboursement_details.remboursement_id')
            ->select('remboursements.*', 'remboursement_details.montant_rembourse')
            ->get();

        // return response()->json(['remboursements' => $remboursements]);
        return response()->json([
            'success' => true,
            'data' =>  $remboursements,
        ], 200);
    }

    // public function showEligiblePatients()
    // {
    //     $patients = DB::table('patients')
    //         ->join('patient_insurances', 'patients.id', '=', 'patient_insurances.patients_id')
    //         ->join('packs', 'patient_insurances.pack_id', '=', 'packs.id')
    //         ->where('patient_insurances.date_fin', '>=', now())
    //         ->select('patients.*')
    //         ->get();

    //     // return response()->json(['patients' => $patients]);
    //     return response()->json([
    //         'success' => true,
    //         'data' =>  $patients,
    //     ], 200);
    // }
    public function showEligiblePatients()
    {
        $patients = DB::table('patients')
            ->join('patient_insurances', 'patients.id', '=', 'patient_insurances.patients_id')
            ->join('packs', 'patient_insurances.pack_id', '=', 'packs.id')
            ->where('patient_insurances.date_fin', '>=', now())
            ->whereExists(function ($query) {
                // Utilisez whereColumn pour faire référence aux colonnes de la table externe
                $query->select(DB::raw(1))
                    ->from('factures')
                    ->join('movments', 'factures.movments_id', '=', 'movments.id')
                    ->whereColumn('movments.patients_id', '=', 'patients.id')
                    ->where('factures.paid', '=', 1);
            })
            ->select('patients.*')
            ->get();

        return response()->json([
            'success' => true,
            'data' =>  $patients,
        ], 200);
    }




    // public function getPaymentDetails($patientId)
    // {
    //     try {
    //         // Récupérer les détails du paiement en fonction de l'ID du patient
    //         $refundDetails = DB::table('factures')
    //         ->join('movments', 'factures.movments_id', '=', 'movments.id')
    //         ->join('patient_insurances', 'movments.patients_id', '=', 'patient_insurances.patients_id')
    //         ->join('packs', 'patient_insurances.pack_id', '=', 'packs.id')
    //         ->where('factures.paid', 1)
    //         ->where('patient_insurances.patients_id', $patientId)
    //         ->select('factures.*', 'packs.percentage as assurance_percentage')
    //         ->get();


    //         return response()->json([
    //             'success' => true,
    //             'data' => $refundDetails,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         // Gérer les erreurs si les détails de paiement ne sont pas trouvés
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erreur lors de la récupération des détails de paiement.',
    //         ], 500);
    //     }
    // }

    public function getPaymentDetails($patientId)
    {
        try {
            // Récupérer les détails du paiement en fonction de l'ID du patient
            DB::enableQueryLog();
            $refundDetails = DB::table('factures')
                ->join('movments', 'factures.movments_id', '=', 'movments.id')
                ->join('patient_insurances', 'movments.patients_id', '=', 'patient_insurances.patients_id')
                ->join('packs', function ($join) {
                    $join->on('patient_insurances.pack_id', '=', 'packs.id')
                        ->where('packs.percentage', '>', 0) // Assurez-vous que le pourcentage du pack est supérieur à 0
                        ->where('patient_insurances.date_debut', '<=', now()) // La période de validité du pack a commencé
                        ->where('patient_insurances.date_fin', '>=', now()); // La période de validité du pack n'est pas encore expirée
                })
                // ->leftJoin('remboursement_details', 'factures.id', '=', 'remboursement_details.facture_id')
                ->where('factures.paid', 1)
                ->whereBetween('factures.created_at', [DB::raw('patient_insurances.date_debut'), DB::raw('patient_insurances.date_fin')])
                ->where('patient_insurances.patients_id', $patientId)
                // ->whereNull('remboursement_details.facture_id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('remboursement_details')
                        ->whereRaw('remboursement_details.facture_id = factures.id');
                })
                ->select('factures.*', 'packs.percentage as assurance_percentage')
                ->get();

            // \Log::info(DB::getQueryLog());
            return response()->json([
                'success' => true,
                'data' => $refundDetails,
            ], 200);
        } catch (\Exception $e) {
            // Gérer les erreurs si les détails de paiement ne sont pas trouvés
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails de paiement.',
            ], 500);
        }
    }



    public function processRefund(Request $request, $patientId)
    {

        $refundDetails =
            // DB::table('factures')
            //     ->join('movments', 'factures.movments_id', '=', 'movments.id')
            //     ->join('patient_insurances', 'movments.patients_id', '=', 'patient_insurances.patients_id')
            //     ->join('packs', 'patient_insurances.pack_id', '=', 'packs.id')
            //     ->where('factures.paid', 1)
            //     ->where('patient_insurances.patients_id', $patientId)
            //     ->select('factures.*', 'packs.percentage as assurance_percentage')
            //     ->get();
            DB::table('factures')
            ->join('movments', 'factures.movments_id', '=', 'movments.id')
            ->join('patient_insurances', 'movments.patients_id', '=', 'patient_insurances.patients_id')
            ->join('packs', function ($join) {
                $join->on('patient_insurances.pack_id', '=', 'packs.id')
                    ->where('packs.percentage', '>', 0) // Assurez-vous que le pourcentage du pack est supérieur à 0
                    ->where('patient_insurances.date_debut', '<=', now()) // La période de validité du pack a commencé
                    ->where('patient_insurances.date_fin', '>=', now()); // La période de validité du pack n'est pas encore expirée
            })
            // ->leftJoin('remboursement_details', 'factures.id', '=', 'remboursement_details.facture_id')
            ->where('factures.paid', 1)
            ->whereBetween('factures.created_at', [DB::raw('patient_insurances.date_debut'), DB::raw('patient_insurances.date_fin')])
            ->where('patient_insurances.patients_id', $patientId)
            // ->whereNull('remboursement_details.facture_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('remboursement_details')
                    ->whereRaw('remboursement_details.facture_id = factures.id');
            })
            ->select('factures.*', 'packs.percentage as assurance_percentage')
            ->get();


        foreach ($refundDetails as $detail) {
            $amountToRefund = $detail->amount * ($detail->assurance_percentage / 100);

            $remboursementId = DB::table('remboursements')->insertGetId([
                'patient_id' => $patientId,
                'date_remboursement' => now(),
                // Ajoutez d'autres champs si nécessaire
            ]);

            DB::table('remboursement_details')->insert([
                'remboursement_id' => $remboursementId,
                'facture_id' => $detail->id,
                'montant_rembourse' => $amountToRefund,
                // Ajoutez d'autres champs si nécessaire
            ]);

            // Mettez à jour la facture ou effectuez d'autres opérations si nécessaire
            // ...
        }

        return response()->json(['message' => 'Remboursement effectué avec succès']);
    }


    //     function getRefundedInvoices($patientId, $invoiceReference, $startDate, $endDate)
    // {
    //     $invoices = DB::table('factures')
    //         ->join('movments', 'factures.movments_id', '=', 'movments.id')
    //         ->join('patient_insurances', 'movments.patients_id', '=', 'patient_insurances.patients_id')
    //         ->join('packs', 'patient_insurances.pack_id', '=', 'packs.id')
    //         ->where('factures.paid', 1)
    //         ->where('patient_insurances.patients_id', $patientId)
    //         ->where('factures.reference', $invoiceReference)
    //         ->whereBetween('factures.created_at', [$startDate, $endDate])
    //         ->where('patient_insurances.date_debut', '<=', now())
    //         ->where('patient_insurances.date_fin', '>=', now())
    //         ->where('factures.percentageassurance', '=', 0) // Ajoutez cette condition pour vérifier si le pourcentage n'a pas été appliqué
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $invoices,
    //     ], 200);
    // }
    function getRefundedInvoices($invoiceReference, $startDate, $endDate)
    {
        $invoices = DB::table('factures')
            ->join('movments', 'factures.movments_id', '=', 'movments.id')
            ->join('patient_insurances', 'movments.patients_id', '=', 'patient_insurances.patients_id')
            ->join('packs', 'patient_insurances.pack_id', '=', 'packs.id')
            ->join('patients', 'movments.patients_id', '=', 'patients.id')
            ->where('factures.paid', 1)
            ->where('factures.reference', $invoiceReference)
            ->whereBetween('factures.created_at', [$startDate, $endDate])
            ->where('patient_insurances.date_debut', '<=', now())
            ->where('patient_insurances.date_fin', '>=', now())
            ->where('factures.percentageassurance', '=', 0) // Ajoutez cette condition pour vérifier si le pourcentage n'a pas été appliqué
            ->select(
                'factures.*',
                'patients.firstname as patient_firstname',
                'patients.lastname as patient_lastname'
            ) // Ajout de la clause select
            ->get();

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ], 200);
    }
    public function updatePercentage(Request $request, $invoiceId, $percentage)
    {
        try {
            DB::table('factures')
                ->where('id', $invoiceId)
                ->update(['percentageassurance' => $percentage]);

            return response()->json([
                'success' => true,
                'message' => 'Le pourcentage a été mis à jour avec succès.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du pourcentage.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    // public function getFacturesPayeesNonDestockees(Request $request, $invoiceReference)
    // {
    //     // $referenceFacture = $request->input('reference_facture');

    //     $invoices = DB::table('factures')
    //         ->join('movments', 'factures.movments_id', '=', 'movments.id')
    //         ->join('patients', 'movments.patients_id', '=', 'patients.id')
    //         ->leftJoin('destocks', function ($join) {
    //             $join->on('factures.reference', '=', 'destocks.reference_facture')
    //                 ->where('destocks.quantity_retrieved', '>', 0);
    //         })
    //         ->where('factures.reference', $invoiceReference)
    //         ->where('factures.type', 'P')
    //         ->where('factures.is_factured', 1)
    //         ->where('factures.paid', 1)
    //         ->select(
    //             'factures.*',
    //             'patients.firstname as patient_firstname',
    //             'patients.lastname as patient_lastname'
    //         )
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $invoices,
    //     ], 200);
    // }

    // public function getFacturesPayeesNonDestockees(Request $request, $invoiceReference)
    // {
    //     $invoices = DB::table('factures')
    //         ->join('movments', 'factures.movments_id', '=', 'movments.id')
    //         ->join('patients', 'movments.patients_id', '=', 'patients.id')
    //         ->leftJoin('destocks', function ($join) {
    //             $join->on('factures.reference', '=', 'destocks.reference_facture')
    //                 ->where('destocks.quantity_retrieved', '>', 0);
    //         })
    //         ->where('factures.reference', $invoiceReference)
    //         ->where('factures.type', 'P')
    //         ->where('factures.is_factured', 1)
    //         ->where('factures.paid', 1)
    //         // ->whereNull('destocks.reference_facture')  // Exclure les factures avec déstockage
    //         ->select(
    //             'factures.*',
    //             'patients.firstname as patient_firstname',
    //             'patients.lastname as patient_lastname'
    //         )
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $invoices,
    //     ], 200);
    // }

    public function getFacturesPayeesNonDestockees(Request $request, $invoiceReference)
    {
        $invoices = DB::table('factures')
            ->join('movments', 'factures.movments_id', '=', 'movments.id')
            ->join('patients', 'movments.patients_id', '=', 'patients.id')
            ->leftJoin('destocks', function ($join) {
                $join->on('factures.reference', '=', 'destocks.reference_facture')
                    ->where('destocks.quantity_retrieved', '>', 0);
            })
            ->where('factures.reference', $invoiceReference)
            ->where('factures.type', 'P')
            ->where('factures.is_factured', 1)
            ->where('factures.paid', 1)
            ->whereNull('destocks.reference_facture')  // Exclure les factures avec déstockage
            ->where(function ($query) {
                $query->where('factures.rembourse_effectue', null)
                    ->orWhere('factures.rembourse_effectue', 0);
            })  // Ajouter la condition rembourse_effectue
            ->select(
                'factures.*',
                'patients.firstname as patient_firstname',
                'patients.lastname as patient_lastname'
            )
            ->get();

        $invoicesData = $invoices->map(function ($invoice) {
            // Si les produits de la facture n'ont pas été déstockés,
            // alors le montant payé est égal au montant à rembourser
            $invoice->amount_to_reimburse = $invoice->amount;

            return $invoice;
        });

        return response()->json([
            'success' => true,
            'data' => $invoicesData,
        ], 200);
    }

    public function updateFactureRemboursement($factureId)
    {
        try {
            // Récupérer la facture à partir de l'ID
            $facture = Facture::findOrFail($factureId);

            // Mettre à jour le champ "rembourse_effectue" à true
            $facture->rembourse_effectue = true;

            // Enregistrez les modifications dans la base de données
            $facture->save();

            // Répondre avec une confirmation
            return response()->json(['message' => 'La facture a été marquée comme remboursée avec succès.']);
        } catch (\Exception $e) {
            // En cas d'erreur, répondre avec un message d'erreur
            return response()->json(['error' => 'Une erreur est survenue lors de la mise à jour de la facture.'], 500);
        }
    }

    // public function getFacturesRemboursees()
    // {
    //     // Utilisez le Query Builder de Laravel pour construire la requête SQL
    //     $facturesRemboursees = Facture::where('percentageassurance', '>', 0)
    //         ->where('rembourse_effectue', '>', 0)
    //         ->where('is_factured', 1)
    //         ->where('paid', 1)
    //         ->get();

    //     // Retournez les résultats en tant que réponse JSON
    //     return response()->json($facturesRemboursees);
    // }

    public function generateFactureWithCaissierName($factureId)
    {
        // Récupérez la facture avec l'ID fourni
        $facture = Facture::find($factureId);

        if (!$facture) {
            return response()->json(['error' => 'Facture non trouvée.'], 404);
        }

        // Récupérez le nom et le prénom du caissier associé à la facture
        $caissier = DB::table('users')
            ->join('factures', 'users.id', '=', 'factures.user_id')
            ->where('factures.id', $factureId)
            ->select('users.name', 'users.prenom')  // Sélectionnez le nom et le prénom du caissier
            ->first();

        // Vérifiez si le caissier a été trouvé
        if ($caissier) {
            // Ajoutez le nom du caissier aux données de la facture
            $facture->caissier_name = $caissier->name;
            $facture->caissier_prenom = $caissier->prenom;
        } else {
            // Gérez le cas où le caissier n'a pas été trouvé
            $facture->caissier_name = 'Inconnu';
            $facture->caissier_prenom = 'Inconnu';
        }

        // Retournez la réponse JSON avec les données mises à jour
        return response()->json(['facture' => $facture]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('remboursement::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('remboursement::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('remboursement::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
