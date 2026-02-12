<?php

namespace Modules\Payment\Http\Controllers\Api\V1;
// use PDF;
use Illuminate\Support\Facades\File;
// use Modules\Payment\Config\dompdf as PDF;
use Barryvdh\DomPDF\Facade\Pdf;
use RecursiveIteratorIterator;


use RecursiveDirectoryIterator;

use Modules\Payment\Http\Controllers\PaymentController;

use  Modules\Stock\Http\Controllers\Api\V1\ProductController;


use Modules\Stock\Entities\ConditioningUnit;
use Modules\Stock\Entities\SaleUnit;
use Modules\Stock\Entities\Product;
use Modules\Stock\Entities\StockProduct;

use Modules\Payment\Entities\Facture;
use App\Models\Momo;
use Modules\Cash\Entities\CashRegister;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use PatricPoba\MtnMomo\MtnCollection;
use PatricPoba\MtnMomo\MtnConfig;
use Kkiapay\Kkiapay;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use Modules\Patient\Entities\PatientInsurance;
use Modules\Patient\Entities\Patiente;
use Modules\Administration\Entities\MedicalAct;
use SimpleSoftwareIO\QrCode\Facades\QrCode;




class FactureController extends PaymentController
{


    // public function getMedicalActDetailsForMovment($searchParam)
    // {
    //     $medicalActDetails = DB::table('patient_movement_details')
    //         ->join('medical_acts', 'patient_movement_details.medical_acts_id', '=', 'medical_acts.id')
    //         ->join('movments', 'patient_movement_details.movments_id', '=', 'movments.id')
    //         ->join('patients', 'movments.patients_id', '=', 'patients.id')
    //         ->select('medical_acts.id', 'medical_acts.code', 'medical_acts.designation', 'medical_acts.price', 'patient_movement_details.quantite', 'movments.id as movments_id')
    //         ->where('patient_movement_details.solved', 0) // Ajout de la condition solved = 0
    //         ->where(function ($query) use ($searchParam) {
    //             $searchTerms = preg_split('/\s+/', $searchParam); // Sépare les termes par des espaces

    //             foreach ($searchTerms as $term) {
    //                 $query->orWhere(function ($subquery) use ($term) {
    //                     $subquery->whereRaw("CONCAT(patients.lastname, ' ', patients.firstname) LIKE ?", ['%' . $term . '%']);
    //                 });
    //             }
    //         })
    //         ->orWhere('patients.ipp', $searchParam) // Recherche par ipp
    //         ->orWhere('movments.id', $searchParam) // Recherche par movments.id
    //         ->get();

    //     return response()->json(['data' => $medicalActDetails]);
    // }


    public function getPostPdf()
    {
        $htmlContent = view('payment::pdf.facture')->render();

        // Générer le PDF à partir du HTML
        $pdf = PDF::loadHTML($htmlContent);

        // Renvoyer le PDF en réponse avec le nom de fichier spécifié

        // return $pdf->download('facture.pdf');

        return view('payment::pdf.facture');
    }


    public function getMedicalActDetailsForMovment($movmentId)
    {


        $medicalActDetails = DB::table('patient_movement_details')
            ->join('movments', 'patient_movement_details.movments_id', '=', 'movments.id')
            ->join('patients', 'movments.patients_id', '=', 'patients.id')
            ->where('patient_movement_details.paid', 0)
            ->where('movments.id', $movmentId)
            ->where(function ($query) {
                $query->where('patient_movement_details.type', 'A')
                    ->orWhere('patient_movement_details.type', 'P');
            });

        $result = $medicalActDetails
            ->leftJoin('medical_acts', function ($join) {
                $join->on('patient_movement_details.medical_acts_id', '=', 'medical_acts.uuid')
                    ->where('patient_movement_details.type', '=', 'A');
            })
            ->leftJoin('products', function ($join) {
                $join->on('patient_movement_details.medical_acts_id', '=', 'products.uuid')
                    ->where('patient_movement_details.type', '=', 'P');
            })
            ->select(
                'movments.id as movments_id',
                'patient_movement_details.id as id',
                'patient_movement_details.medical_acts_id',
                'patient_movement_details.type',
                DB::raw('CASE
                    WHEN patient_movement_details.type = "A" THEN medical_acts.code
                    WHEN patient_movement_details.type = "P" THEN products.code
                    ELSE NULL
                    END AS code'),
                DB::raw('CASE
                    WHEN patient_movement_details.type = "A" THEN medical_acts.designation
                    WHEN patient_movement_details.type = "P" THEN CONCAT(products.id)
                    ELSE NULL
                END AS designation'),

                DB::raw('CASE
                WHEN patient_movement_details.type = "P" THEN CONCAT(products.uuid)
                ELSE NULL
                END AS uuid'),

                'patient_movement_details.medical_acts_qte',
                'patient_movement_details.medical_acts_price'
            )
            ->get();

        $result = $result->map(function ($item) {
            // Vérifier si le type est "P" et la désignation est un identifiant de produit
            if ($item->type == 'P' && is_numeric($item->designation)) {
                // Mettre à jour la désignation en utilisant getProductFormatted
                $item->designation = $this->getProductFormatted($item->designation);
            }

            return $item;
        });

        return response()->json(['data' => $result]);
    }

    public function getListProductByReference($reference)
    {
        $productDetails = DB::table('factures')
            ->join('movments', 'factures.movments_id', '=', 'movments.id')
            ->join('stock_products', 'factures.lots_uuid', '=', 'stock_products.uuid')
            ->where(function ($query) use ($reference) {
                $query->where('factures.paid', 1)
                    ->orWhereNotNull('factures.partial_amount');
            })
            ->where('factures.reference', $reference)
            ->where('factures.type', 'P')
            ->select(
                'movments.id as movments_id',
                'movments.iep as iep',
                'factures.uuid as facture_uuid',
                'factures.acte_medical_id',
                'factures.lots_uuid',
                'stock_products.lot_number',
                'stock_products.quantity as available_quantity',
                'stock_products.expire_date',
                'factures.type',
                'factures.code',
                'factures.designation',
                'factures.prix',
                'factures.quantite'
            )->get();

        return response()->json(['data' => $productDetails]);
    }



    public function getProductByReference($reference)
    {

        $medicalActDetails = DB::table('factures')

            ->where('factures.paid', 1)
            ->where('reference', $reference)
            ->select(
                'factures.acte_medical_id',
                'factures.quantite',
                'factures.prix',
                'factures.reference',
            )
            ->get();

        return response()->json(['data' => $medicalActDetails]);
    }



    public function getProductFormatted($identifiant)
    {


        if (!isset($identifiant)) {
            $data = [
                "message" => __("Identifiant manquant"),
            ];
            return response()->json($data, 400);
        }

        $product = Product::find($identifiant);




        if ($product) {
            $conditioningUnit = ConditioningUnit::find($product->conditioning_unit_id);
            $saleUnit = SaleUnit::find($product->sale_unit_id);

            $productFormatted = $product->name . ' ' . $conditioningUnit->name . ' ' . $saleUnit->name;
            return  $productFormatted;
        } else {

            return "NAN";
        }
    }








    public function listMovment()
    {

        $result = DB::table('movments')
            ->select('movments.id', 'patients.ipp', 'patients.lastname', 'patients.firstname', 'movments.arrivaldate', 'patients.phone')
            ->Join('patients', 'patients.id', '=', 'movments.patients_id')
            ->whereNull('movments.releasedate')
            ->limit(10)
            ->get();

        return response()->json(['data' => $result]);
    }

    // public function searchMovments(Request $request)
    // {
    //     $query = DB::table('movments')
    //         ->select('movments.id', 'patients.ipp', 'patients.lastname', 'patients.firstname', 'movments.arrivaldate', 'patients.phone')
    //         ->join('patients', 'patients.id', '=', 'movments.patients_id');
    //     if ($request->has('search')) {
    //         $search = $request->input('search');
    //         $query->where(function ($query) use ($search) {
    //             $query->where('movments.arrivaldate', 'like', '%' . $search . '%')
    //                 ->orWhere('movments.id', 'like', '%' . $search . '%')
    //                 ->orWhere('patients.ipp', 'like', '%' . $search . '%')
    //                 ->orWhere(DB::raw("CONCAT(patients.lastname, ' ', patients.firstname)"), 'like', '%' . $search . '%');
    //         });
    //     }

    //     $result = $query->whereNull('movments.releasedate')
    //         ->limit(10)
    //         ->get();

    //     return response()->json(['data' => $result]);

    // }

    public function listInsurancePatient($patientId)
    {
        $patientInsurances = DB::table('patient_insurances')
            ->where('patient_id', $patientId)
            ->join('packs', 'patient_insurances.packs_id', '=', 'packs.id')
            ->select('packs.designation', 'packs.percentage')
            ->get();

        return response()->json(['patient_insurances' => $patientInsurances]);
    }


    public function searchMovments(Request $request)
    {
        $query = DB::table('movments')
            ->select('movments.id', 'movments.iep', 'patients.ipp', 'patients.lastname', 'patients.firstname', DB::raw('DATE_FORMAT(movments.arrivaldate, "%d/%m/%Y") as formatted_arrivaldate'), 'patients.phone')
            ->join('patients', 'patients.id', '=', 'movments.patients_id');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($query) use ($search) {
                $query->where(DB::raw('DATE_FORMAT(movments.arrivaldate, "%d/%m/%Y")'), 'like', '%' . $search . '%')
                    ->orWhere('movments.iep', 'like', '%' . $search . '%')
                    ->orWhere('patients.ipp', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("CONCAT(patients.lastname, ' ', patients.firstname)"), 'like', '%' . $search . '%');
            });
        }

        // Ajoutez une clause pour récupérer les derniers mouvements
        $query->whereNull('movments.releasedate')
            ->orderBy('movments.arrivaldate', 'desc') // Triez par date d'arrivée décroissante
            ->limit(10); // Limitez à 10 résultats

        $result = $query->get();

        return response()->json(['data' => $result]);
    }






    // public function getMedicalActDetailsForMovment($searchParam)
    // {
    //     $medicalActDetails = DB::table('patient_movement_details')
    //         ->join('medical_acts', 'patient_movement_details.medical_acts_id', '=', 'medical_acts.id')
    //         ->join('movments', 'patient_movement_details.movments_id', '=', 'movments.id')
    //         ->join('patients', 'movments.patients_id', '=', 'patients.id')
    //         ->select('medical_acts.id', 'medical_acts.code', 'medical_acts.designation', 'medical_acts.price')
    //         ->where('patient_movement_details.solved', 0) // Ajout de la condition solved = 0
    //         ->where(function ($query) use ($searchParam) {
    //             $searchTerms = preg_split('/\s+/', $searchParam); // Sépare les termes par des espaces

    //             foreach ($searchTerms as $term) {
    //                 $query->whereRaw("CONCAT(patients.lastname, ' ', patients.firstname) LIKE ?", ['%' . $term . '%']);
    //             }
    //         })
    //         ->get();

    //     return response()->json(['data' => $medicalActDetails]);
    // }


    public function getActe(string $id)
    {
        // Recherche de l'acte médical par ID
        $medicalAct = DB::table('medical_acts')->find($id);

        // Vérification si l'acte médical existe
        if (!$medicalAct) {
            return response()->json([
                'success' => false,
                'message' => 'Acte médical introuvable.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $medicalAct,
            'message' => 'Détails de l\'acte médical récupérés avec succès.'
        ]);
    }



    public function getPatientInfo($searchParam)
    {
        $result = DB::table('movments')
            ->join('patients', 'movments.patients_id', '=', 'patients.id')
            ->select('patients.id', 'patients.lastname', 'patients.firstname', 'patients.phone', 'patients.maison', 'patients.ipp', 'patients.gender', 'patients.email', 'movments.iep as iep')
            ->where('movments.id', $searchParam)
            ->first(); // Utilisez first() pour obtenir le premier résultat

        if (!$result) {
            return response()->json(['message' => 'Patient introuvable'], 404);
        }

        return response()->json(['data' => $result]);
    }




    public function kkiapay($transaction_id)
    {

        $public_key = "d9da5d50d3a311edb532ad421d393c9e";
        $private_key = "tpk_d9da8461d3a311edb532ad421d393c9e";
        $secret = "tsk_d9da8462d3a311edb532ad421d393c9e";
        $sandbox = true;

        // $public_key = "32e01f86a0acd87fbb91d17f1a77a547d5d46c4e";
        // $private_key = "pk_85012e6933f3b4b6d92b29d98d029eaf5644c3dbea35639c76f4d999704bd27b";
        // $secret = "sk_efad825413c963347005131fa3940e3ea5a5b086928695ab05be6596524dd13c";
        // $sandbox = false;


        $kkiapay = new  \Kkiapay\Kkiapay(
            $public_key,
            $private_key,
            $secret,
            $sandbox
        );

        $retour =  $kkiapay->verifyTransaction($transaction_id);

        return response()->json([
            'message' => 'payement créée avec succès !!!',
            'data' =>   $retour,
        ]);
    }






    // public function store(Request $request)
    // {

    //     $data = $request->all();


    //     $montantTotal = 0; // Initialisez le montant total à zéro

    //     $annee = date('y');
    //     $mois = date('m');
    //     $jour = date('d');

    //     $codeAleatoire = mt_rand(10000000, 99999999);
    //     $codeUnique = $annee . $mois . $jour . '-' . $codeAleatoire;

    //     while (Facture::where('code', $codeUnique)->exists()) {
    //         $codeAleatoire = mt_rand(10000000, 99999999);
    //         $codeUnique = $annee . $mois . $jour . '-' . $codeAleatoire;
    //     }

    //     $facturesData = $data['factures'];
    //     $factures = [];

    //     $patientInfo = null;


    //     foreach ($facturesData as $factureData) {

    //         $validatedData = Validator::make($factureData, [
    //             'cash_registers_id' => 'nullable',
    //             'movments_id' => 'required',
    //             'mode_payements_id' => 'nullable',
    //             'acte_medical_id' => 'nullable|integer',
    //             'code' => 'nullable|string',
    //             'designation' => 'nullable|string',
    //             'type' => 'nullable|string',
    //             'autre' => 'nullable|string',
    //             'prix' => 'required|integer',
    //             'quantite' => 'required|integer',
    //             'montant' => 'required|integer',
    //             'percentageassurance' => 'nullable|integer',

    //         ])->validate();

    //         $facture = Facture::create([
    //             // 'cash_registers_id' => $validatedData['cash_registers_id'],
    //             'cash_registers_id' => $validatedData['cash_registers_id'],
    //             'movments_id' => $validatedData['movments_id'],
    //             'mode_payements_id' => $validatedData['mode_payements_id'],
    //             'is_synced' => 0,
    //             'reference' => $codeUnique,
    //             'acte_medical_id' => $validatedData['acte_medical_id'],
    //             'user_id' => 1,
    //             'centre_id' => null,
    //             'code' => $validatedData['code'],
    //             'designation' => $validatedData['designation'],
    //             'type' => $validatedData['type'],
    //             'autre' => null,
    //             'prix' => $validatedData['prix'],
    //             'quantite' => $validatedData['quantite'],
    //             'montant' => $validatedData['montant'],
    //             'percentageassurance' => $validatedData['percentageassurance'],
    //         ]);

    //         // $montantTotal += $validatedData['montant'];

    //         // if ($validatedData['acte_medical_id'] !== null) {
    //         //     DB::table('patient_movement_details')
    //         //         ->where('medical_acts_id', $validatedData['acte_medical_id'])
    //         //         ->update(['paid' => 1]);
    //         // }

    //         $factures[] = $facture;
    //     }

        // $cashRegister = CashRegister::find($validatedData['cash_registers_id']);


        // if ($validatedData['mode_payements_id'] != 8) {
        //     // Mettez à jour le solde dans la table cash_registers
        //     // $cashRegister = CashRegister::find($validatedData['cash_registers_id']);
        //     $cashRegister->solde += $montantTotal;
        //     $cashRegister->save();
        // } elseif ($validatedData['mode_payements_id'] == 8) {
        //     // Mettez à jour le montant dans la table credits
        //     // $cashRegister = CashRegister::find($validatedData['cash_registers_id']);
        //     $cashRegister->credits += $montantTotal;
        //     $cashRegister->save();
        // }

    //     $movmentId = isset($factures[0]['movments_id']) ? $factures[0]['movments_id'] : null;
    //     if ($movmentId !== null) {
    //         // Récupérez les informations du patient pour le movments_id
    //         $patientInfoResponse = $this->getPatientInfo($movmentId);

    //         if ($patientInfoResponse->getStatusCode() == 200) {
    //             $patientInfo = json_decode($patientInfoResponse->getContent())->data;
    //         } else {
    //             // Gérez le cas où la requête pour obtenir les informations du patient a échoué
    //             return response()->json(['error' => 'Erreur lors de la récupération des informations du patient'], 500);
    //         }
    //     }


    //     $pdfPaths = $this->generatePdf($factures, $codeUnique, $patientInfo);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Factures enregistrées avec succès.',
    //         'data' => $factures,
    //         'pdf_url_prestation' => $pdfPaths['pdf_url_prestation'],
    //         'pdf_url_pharmacie' => $pdfPaths['pdf_url_pharmacie'],
    //         'reference' => $codeUnique,
    //     ], 201);

    //     // return response()->json([
    //     //     'success' => true,
    //     //     'message' => 'Factures enregistrées avec succès.',
    //     //     'data' => $factures,
    //     //     'reference' => $codeUnique,
    //     //     // 'statutCash' => $statut,
    //     // ], 201);

    // }



    public function store(Request $request)
    {

        $data = $request->all();


        $montantTotal = 0; // Initialisez le montant total à zéro


        $facturesData = $data['factures'];

        // Initialisation des groupes de factures
        $facturesTypeA = [];
        $facturesTypeP = [];

        $patientInfo = null;


        foreach ($facturesData as $factureData) {

            $validatedData = Validator::make($factureData, [
                'cash_registers_id' => 'nullable',
                'movments_id' => 'required',
                'mode_payements_id' => 'nullable',
                'acte_medical_id' => 'nullable',
                'lots_uuid' => 'nullable',
                'code' => 'nullable|string',
                'designation' => 'nullable|string',
                'type' => 'nullable|string',
                'prix' => 'required',
                'quantite' => 'required|integer',
                'amount' => 'required',
                'percentageassurance' => 'nullable|integer',

            ])->validate();

            $facture = Facture::create([
                // 'cash_registers_id' => $validatedData['cash_registers_id'],
                'cash_registers_id' => $validatedData['cash_registers_id'],
                'movments_id' => $validatedData['movments_id'],
                'mode_payements_id' => $validatedData['mode_payements_id'],
                'is_synced' => 0,
                'reference' => '',
                'acte_medical_id' => $validatedData['acte_medical_id'],
                'lots_uuid' => $validatedData['lots_uuid'],
                'user_id' => 1,
                'centre_id' => null,
                'code' => $validatedData['code'],
                'designation' => $validatedData['designation'],
                'type' => $validatedData['type'],
                'partial_amount' => null,
                'prix' => $validatedData['prix'],
                'quantite' => $validatedData['quantite'],
                'amount' => $validatedData['amount'],
                'paid' => 0,
                'percentageassurance' => $validatedData['percentageassurance'],
            ]);


            if ($factureData['type'] == 'A') {
                $facturesTypeA[] = $facture;
            } elseif ($factureData['type'] == 'P') {
                $facturesTypeP[] = $facture;
            }

            if ($validatedData['acte_medical_id'] !== null) {
                DB::table('patient_movement_details')
                    ->where('medical_acts_id', $validatedData['acte_medical_id'])
                    ->where('type', $validatedData['type'])
                    ->update(['paid' => 1]); 
            }

        }

        // Générer une référence distincte pour chaque groupe
        $codeUniqueTypeA = $this->generateUniqueCode();
        $codeUniqueTypeP = $this->generateUniqueCode();

        foreach ($facturesTypeA as $factureTypeA) {
            $factureTypeA->update(['reference' => $codeUniqueTypeA]);
        }

        foreach ($facturesTypeP as $factureTypeP) {
            $factureTypeP->update(['reference' => $codeUniqueTypeP]);
        }

        $movmentId = null;

        if (!empty($facturesTypeA)) {
            $movmentId = $facturesTypeA[0]['movments_id'];
        } elseif (!empty($facturesTypeP)) {
            $movmentId = $facturesTypeP[0]['movments_id'];
        }

        if ($movmentId !== null) {
            // Récupérez les informations du patient pour le movments_id
            $patientInfoResponse = $this->getPatientInfo($movmentId);

            if ($patientInfoResponse->getStatusCode() == 200) {
                $patientInfo = json_decode($patientInfoResponse->getContent())->data;
            } else {
                return response()->json(['error' => 'Erreur lors de la récupération des informations du patient'], 500);
            }
        }

        $pdfPathsTypeA = $this->generatePdf($facturesTypeA, $codeUniqueTypeA, $patientInfo);
        $pdfPathsTypeP = $this->generatePdf($facturesTypeP, $codeUniqueTypeP, $patientInfo);

        return response()->json([
            'success' => true,
            'message' => 'Factures enregistrées avec succès.',
            'data_type_a' => $facturesTypeA,
            'data_type_p' => $facturesTypeP,
            'pdf_url_prestation' => $pdfPathsTypeA['pdf_url_prestation'],
            'pdf_url_pharmacie' =>  $pdfPathsTypeP['pdf_url_pharmacie'],
            'reference_type_a' => $codeUniqueTypeA,
            'reference_type_p' => $codeUniqueTypeP,
        ], 201);
    }


    public function payementFacture($reference, $modePayementsId, $partial_amount = null)
    {

        $updateData = [
            'paid' => 1,
            'mode_payements_id' => $modePayementsId,
            'created_at' => Carbon::now(),
        ];

        // Mise à jour de la colonne partial_amount si elle est fournie
        if ($partial_amount !== null) {

            $updateData['partial_amount'] = $partial_amount;
        }

        Facture::where('reference', $reference)->update($updateData);


        // $factures = Facture::where('reference', $reference)->get();
        // $facture = Facture::where('reference', '=',  $reference)->first();


        // $movmentId = $facture->movments_id;

        // if ($movmentId !== null) {
        //     // Récupérez les informations du patient pour le movments_id
        //     $patientInfoResponse = $this->getPatientInfo($movmentId);

        //     if ($patientInfoResponse->getStatusCode() == 200) {
        //         $patientInfo = json_decode($patientInfoResponse->getContent())->data;
        //     } else {
        //         return response()->json(['error' => 'Erreur lors de la récupération des informations du patient'], 500);
        //     }
        // }


        //  $pdfPaths = $this->generatePdf($facturesArray, $reference, $patientInfo);

        return response()->json([
            'success' => true,
            'message' => 'Facture payée avec succès.',
            // 'pdf_url_prestation' => $pdfPaths['pdf_url_prestation'],
            // 'pdf_url_pharmacie' => $pdfPaths['pdf_url_pharmacie'],
        ], 200);
    }



    private function generatePdf($factures, $codeUnique, $patientInfo)
    {
        $pdfLink = asset('storage/pdf/facture_' . $codeUnique . '.pdf');
        $qrCode = QrCode::size(50)->generate($pdfLink);

        $facturesTypeA = array_filter($factures, function ($facture) {
            return $facture['type'] == 'A';
        });

        $facturesTypeP = array_filter($factures, function ($facture) {
            return $facture['type'] == 'P';
        });


        $htmlContentTypeA = view('payment::pdf.facture-prestation', compact('facturesTypeA', 'codeUnique', 'qrCode', 'patientInfo'))->render();
        $htmlContentTypeP = view('payment::pdf.facture-pharmacie', compact('facturesTypeP', 'codeUnique', 'qrCode', 'patientInfo'))->render();

        $storagePath = public_path('storage/pdf/');

        // Si le dossier n'existe pas, créez-le
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0777, true, true);
        } else {
            // Si le dossier existe, assurez-vous qu'il a les bonnes autorisations
            chmod($storagePath, 0777);

            // Si vous voulez appliquer les autorisations aux sous-répertoires récursivement
            $this->recursiveChmod($storagePath, 0777);
        }


        $pdfPathTypeA = $storagePath . 'facture_prestation_' . $codeUnique . '.pdf';

        $pdfTypeA = PDF::loadHTML($htmlContentTypeA);
        $pdfTypeA->setPaper('a4', 'portrait');
        $pdfTypeA->output(['width' => '100%']);
        $pdfTypeA->save($pdfPathTypeA);

        $pdfPathTypeP = $storagePath . 'facture_pharmacie_' . $codeUnique . '.pdf';
        $pdfTypeP = PDF::loadHTML($htmlContentTypeP);
        $pdfTypeP->setPaper('a4', 'portrait');
        $pdfTypeP->output(['width' => '100%']);
        $pdfTypeP->save($pdfPathTypeP);

        return [
            'pdf_url_prestation' => asset('storage/pdf/facture_prestation_' . $codeUnique . '.pdf'),
            'pdf_url_pharmacie' => asset('storage/pdf/facture_pharmacie_' . $codeUnique . '.pdf'),
        ];
    }


    private function recursiveChmod($dir, $permission)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            chmod($item, $permission);
        }
    }

    private function deletePdfFiles($reference)
    {
        $pdfFilePathPrestation = public_path('storage/pdf/facture_prestation_' . $reference . '.pdf');
        $pdfFilePathPharmacie = public_path('storage/pdf/facture_pharmacie_' . $reference . '.pdf');

        // Supprimez les fichiers PDF s'ils existent
        if (File::exists($pdfFilePathPrestation)) {
            File::delete($pdfFilePathPrestation);
        }

        if (File::exists($pdfFilePathPharmacie)) {
            File::delete($pdfFilePathPharmacie);
        }
    }


    // public function showFacture($reference)
    // {
    //     $pdfPath = public_path("storage/pdf/facture_prestation_{$reference}.pdf");

    //     if (file_exists($pdfPath)) {
    //         return response()->file($pdfPath);
    //     } else {
    //         abort(404, 'PDF not found');
    //     }
    // }

    private function generateUniqueCode()
    {
        // Générez votre code unique ici (similaire à votre code actuel)
        // Assurez-vous que le code généré est unique dans votre contexte
        $annee = date('y');
        $mois = date('m');
        $jour = date('d');
        $codeAleatoire = mt_rand(10000000, 99999999);
        return $annee . $mois . $jour . '-' . $codeAleatoire;
    }

    // private function generatePdf($factures, $codeUnique, $patientInfo)
    // {


    //     $pdfLink = asset('storage/pdf/facture_' . $codeUnique . '.pdf');

    //     $qrCode = QrCode::size(50)->generate($pdfLink);

    //     $htmlContent = view('payment::pdf.facture', compact('factures', 'codeUnique', 'qrCode', 'patientInfo'))->render();

    //     $storagePath = public_path('storage/pdf/');


    //     // Si le dossier n'existe pas, créez-le
    //     if (!File::exists($storagePath)) {
    //         File::makeDirectory($storagePath, 0777, true, true);
    //     } else {
    //         // Si le dossier existe, assurez-vous qu'il a les bonnes autorisations
    //         chmod($storagePath, 0777);

    //         // Si vous voulez appliquer les autorisations aux sous-répertoires récursivement
    //         $this->recursiveChmod($storagePath, 0777);
    //     }

    //     $pdf = PDF::loadHTML($htmlContent);
    //     $pdf->setPaper('a4', 'portrait');

    //     // Set width to 100%
    //     $pdf->output(['width' => '100%']);

    //     $pdfPath = $storagePath . 'facture_' . $codeUnique . '.pdf';
    //     $pdf->save($pdfPath);

    //     return asset('storage/pdf/facture_' . $codeUnique . '.pdf');
    // }


    public function getStatusByReference($reference)
    {
        // Obtenez la valeur de 'paid' directement depuis la table 'factures'
        $statut = DB::table('factures')
            ->where('reference', $reference)
            ->value('paid');

        if ($statut !== null) {
            return response()->json(['statut' => $statut]);
        } else {
            return response()->json(['statut' => 'Aucun enregistrement trouvé']);
        }
    }



    public function getSolde($caisseID)
    {
        $solde = Facture::getSolde($caisseID);
        return response()->json(['data' => $solde]);
    }


    public function index(Request $request)
    {
        $status = $request->input('status');
        $start_date =  $request->input('start_date');
        $end_date = $request->input('end_date');

        $query = DB::table('factures')
            ->select(
                'factures.reference',
                'factures.percentageassurance',
                'factures.movments_id',
                'factures.paid',
                'factures.type',
                'factures.mode_payements_id',
                'factures.created_at',
                DB::raw('SUM(factures.amount) as montant_total'),
                // Ajoutez d'autres colonnes non agrégées ici
                'patients.firstname',
                'patients.lastname'
            )
            ->join('movments', 'movments.id', '=', 'factures.movments_id')
            ->join('patients', 'patients.id', '=', 'movments.patients_id')
            ->groupBy('factures.reference', 'factures.paid', 'factures.type', 'factures.percentageassurance', 'factures.movments_id', 'factures.mode_payements_id', 'factures.created_at', 'patients.firstname', 'patients.lastname')
            ->orderBy('factures.created_at', 'desc');

        if ($status) {
            $query->where('factures.paid', $status);
            // return $query;
        }

        if ($start_date && $end_date) {
            $query->whereDate('factures.created_at', '>=', $start_date)
                ->whereDate('factures.created_at', '<=', $end_date);
        } elseif ($start_date) {
            // Si seule la date de début est renseignée, filtre par la date de début uniquement
            $query->whereDate('factures.created_at', '=', $start_date);
        }

        $factures = $query->get();

        foreach ($factures as $facture) {
            $type = $facture->type;
            $pdfType = $type == 'A' ? 'facture_prestation' : 'facture_pharmacie';

            $pdfPath = asset("storage/pdf/{$pdfType}_{$facture->reference}.pdf");
            $facture->pdfPath = $pdfPath;
        }

        return response()->json([
            'success' => true,
            'data' => $factures,
            'message' => 'Liste des factures récupérées avec succès.'
        ]);
    }


    public function listBillsForsaleProduct(Request $request)
    {
        $query = DB::table('factures')
            ->select(
                'factures.reference',
                'patients.ipp',
                // 'factures.percentageassurance',
                'factures.movments_id',
                'movments.iep',
                'factures.paid',

                'factures.mode_payements_id',
                'factures.created_at',
                DB::raw('SUM(factures.amount) as montant_total'),
                'patients.firstname',
                'patients.lastname',
                'patients.phone',

            )
            ->join('movments', 'movments.id', '=', 'factures.movments_id')
            ->join('patients', 'patients.id', '=', 'movments.patients_id');


        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($query) use ($search) {
                $query->where(DB::raw('DATE_FORMAT(factures.created_at, "%d/%m/%Y")'), 'like', '%' . $search . '%')
                    ->orWhere('patients.ipp', 'like', '%' . $search . '%')
                    ->orWhere('factures.movments_id', 'like', '%' . $search . '%')
                    ->orWhere('factures.reference', 'like', '%' . $search . '%')
                    // ->orWhere('factures.paid', 'like', '%' . $search . '%')
                    ->orWhere('factures.amount', 'like', '%' . $search . '%')
                    ->orWhere('factures.reference', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw("CONCAT(patients.lastname, ' ', patients.firstname)"), 'like', '%' . $search . '%');
            });
        }

        $query->where('factures.type', '=', 'P')
            ->where(function ($query) {
                $query->where('factures.paid', 1)
                    ->orWhereNotNull('factures.partial_amount');
            })
            ->groupBy('factures.reference', 'patients.ipp', 'factures.movments_id', 'movments.iep', 'factures.paid', 'factures.mode_payements_id', 'factures.created_at', 'patients.firstname', 'patients.lastname', 'patients.phone')
            ->orderBy('factures.created_at', 'desc')
            ->limit(10);

        $factures = $query->get();

        return response()->json([
            'success' => true,
            'data' => $factures,
            'message' => 'Liste des factures récupérées avec succès.'
        ]);
    }



    public function getBillsByDate($date)
    {
        $query = DB::table('factures')
            ->select(
                'factures.reference',
                'factures.type',
                'factures.created_at',
                DB::raw('SUM(CASE WHEN factures.partial_amount IS NOT NULL THEN factures.partial_amount ELSE factures.amount END) as montant_total'),
            )
            ->whereDate('factures.created_at', '=', $date)
            ->where('factures.paid', '=', 1)
            ->groupBy('factures.reference', 'factures.type', 'factures.created_at');
    
        $factures = $query->get();
    
        return response()->json([
            'success' => true,
            'data' => $factures,
            'message' => 'Liste des factures récupérées avec succès.'
        ]);
    }
    



    public function listBillsByMovment($movmentId)
    {

        $medicalActDetails = DB::table('factures')
            ->join('movments', 'factures.movments_id', '=', 'movments.id')
            ->join('patients', 'movments.patients_id', '=', 'patients.id')
            ->where('factures.paid', 0)
            ->where('movments.id', $movmentId)
            ->where(function ($query) {
                $query->where('factures.type', 'A')
                    ->orWhere('factures.type', 'P');
            });

        $result = $medicalActDetails
            ->leftJoin('medical_acts', function ($join) {
                $join->on('factures.acte_medical_id', '=', 'medical_acts.uuid')
                    ->where('factures.type', '=', 'A');
            })
            ->leftJoin('products', function ($join) {
                $join->on('factures.acte_medical_id', '=', 'products.uuid')
                    ->where('factures.type', '=', 'P');
            })
            ->select(
                'movments.id as movments_id',
                'factures.id as id',
                'factures.acte_medical_id',
                'factures.type',
                'factures.reference',
                DB::raw('CASE
                        WHEN factures.type = "A" THEN medical_acts.code
                        WHEN factures.type = "P" THEN products.code
                        ELSE NULL
                        END AS code'),
                DB::raw('CASE
                        WHEN factures.type = "A" THEN medical_acts.designation
                        WHEN factures.type = "P" THEN CONCAT(products.id)
                        ELSE NULL
                    END AS designation'),

                'factures.quantite',
                'factures.prix'
            )

            ->get();

        $result = $result->map(function ($item) {
            // Vérifier si le type est "P" et la désignation est un identifiant de produit
            if ($item->type == 'P' && is_numeric($item->designation)) {
                // Mettre à jour la désignation en utilisant getProductFormatted
                $item->designation = $this->getProductFormatted($item->designation);
            }

            return $item;
        });

        return response()->json(['data' => $result]);
    }







    public function getInsuranceDetailsByIpp(Request $request, $ipp)
    {
        // Recherchez le patient en fonction de l'IPP fourni
        $patient = Patiente::where('ipp', $ipp)->first();
        Log::info($patient);
        if ($patient) {
            // Recherchez les informations d'assurance pour ce patient
            $insuranceDetails = PatientInsurance::where('patients_id', $patient->id)
                ->join('packs', 'patient_insurances.pack_id', '=', 'packs.id')
                ->join('insurances', 'packs.insurances_id', '=', 'insurances.id')
                ->select('insurances.number', 'insurances.name as insurance_name', 'packs.designation', 'patient_insurances.date_debut', 'patient_insurances.date_fin', 'packs.percentage')
                ->get();

            if ($insuranceDetails->count() > 0) {
                return response()->json(['data' => $insuranceDetails], 200);
            } else {
                return response()->json(['message' => 'Aucune assurance trouvée pour ce patient.'], 404);
            }
        } else {
            return response()->json(['message' => 'Patient non trouvé.'], 404);
        }
    }






    public function show($reference)
    {
        $factures = DB::table('factures')
            ->select(
                'factures.*',
                'patients.firstname',
                'patients.lastname',
                'factures.code',
                'factures.autre',
                'factures.prix',
                'factures.designation',
                'factures.quantite',
                'factures.amount',
                'factures.created_at'
            )
            ->join('movments', 'movments.id', '=', 'factures.movments_id')
            ->join('patients', 'patients.id', '=', 'movments.patients_id')
            ->where('factures.reference', $reference)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Détails des factures récupérés avec succès.',
            'data' => $factures,
        ], 200);
    }



    // public function show($reference)
    // {
    //     $factures = DB::table('factures')
    //         ->select(
    //             'factures.*',
    //             'patients.firstname',
    //             'patients.lastname',
    //             'factures.code',
    //             'factures.autre',
    //             'factures.prix',
    //             'factures.quantite',
    //             'factures.amount',
    //             'factures.created_at',
    //             DB::raw('CASE
    //                 WHEN pmd.type = "A" THEN ma.designation
    //                 WHEN pmd.type = "P" THEN CONCAT(p.name, " ", p.conditioning_unit, " ", p.sales_unit)
    //                 ELSE NULL
    //                 END AS designation'
    //             )
    //         )
    //         ->join('movments', 'movments.id', '=', 'factures.movments_id')
    //         ->join('patients', 'patients.id', '=', 'movments.patients_id')
    //         ->join('patient_movement_details as pmd', function ($join) {
    //             $join->on('factures.movments_id', '=', 'pmd.movments_id');
    //         })
    //         ->leftJoin('medical_acts as ma', function ($join) {
    //             $join->on('factures.acte_medical_id', '=', 'ma.id')
    //                 ->where('pmd.type', '=', 'A');
    //         })
    //         ->leftJoin('products as p', function ($join) {
    //             $join->on('factures.acte_medical_id', '=', 'p.id')
    //                 ->where('pmd.type', '=', 'P');
    //         })
    //         ->where('factures.reference', $reference)
    //         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Détails des factures récupérés avec succès.',
    //         'data' => $factures,
    //     ], 200);
    // }


}
