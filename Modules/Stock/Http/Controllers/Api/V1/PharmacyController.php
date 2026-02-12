<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Modules\Stock\Http\Controllers\StockController;

use Modules\Payment\Http\Controllers\PaymentController;
use Modules\Payment\Entities\Facture;
use Modules\Stock\Entities\StockProduct;
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


class PharmacyController extends StockController
{

    /**
     * @var PostRepository
     */

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

    public function getProductsForMovement($movmentId)
    {
        //convert $movmentId to number
        $movmentId = (int)$movmentId;

        // $products = DB::table('patient_movement_details')
        //             ->join('products', 'patient_movement_details.medical_acts_id', '=', 'products.id')
        //             ->join('movments', 'patient_movement_details.movments_id', '=', 'movments.id')
        //             ->join('patients', 'movments.patients_id', '=', 'patients.id')
        //             //->join('stock_products', 'patient_movement_details.lot_number', '=', 'stock_products.lot_number')
        //             ->select('movments.id as movments_id','products.id', 'products.code',
        //                     'products.name', 'products.conditioning_unit', 'products.sales_unit',
        //                     'products.dosage', 'products.administration_channel', 'products.margin', 'products.brand', 'patient_movement_details.id as patient_movement_details_id',
        //                     // 'stock_products.quantity as available_quantity', 
        //                     'patient_movement_details.medical_acts_qte as purchased_quantity')
        //             ->where('patient_movement_details.paid', 1)
        //             ->where('patient_movement_details.type', 'P')
        //             ->where('movments.id', $movmentId) // Filtrez par l'ID de la ligne sélectionnée
        //             ->get();

        // return response()->json(['data' => $products]);

        $products = DB::table('patient_movement_details')
            ->join('products', 'patient_movement_details.medical_acts_id', '=', 'products.id')
            ->join('movments', 'patient_movement_details.movments_id', '=', 'movments.id')
            ->join('patients', 'movments.patients_id', '=', 'patients.id')
            ->leftJoin('destock', 'patient_movement_details.id', '=', 'destock.patient_movments_details_id')
            ->select('movments.id as movments_id','products.id', 'products.code',
                'products.name', 'products.conditioning_unit', 'products.sales_unit',
                'products.dosage', 'products.administration_channel', 'products.margin', 'products.brand', 'patient_movement_details.id as patient_movement_details_id',
                'patient_movement_details.medical_acts_qte as purchased_quantity')
            ->where('patient_movement_details.paid', 1)
            ->where('patient_movement_details.type', 'P')
            ->where('movments.id', $movmentId) // Filtrez par l'ID de la ligne sélectionnée
            ->whereNull('destock.patient_movments_details_id') // Exclude rows with a match in the destock table
            ->get();

        return response()->json(['data' => $products]);
    }
    
    public function destock(Request $product)
    {
        $productId = $product['id'];

        // Find the stock product with the given product_id.
        $stockProduct = StockProduct::where('product_id', $productId)->first();
        $patientMovementsDetailsId = $product['patient_movement_details_id'];

        $destock = DB::table('destock')
        ->where('patient_movments_details_id', $patientMovementsDetailsId)
        ->get();

        if ($destock->isEmpty()) {
            if ($stockProduct) {
                $availableQuantity = $stockProduct->quantity;
                $purchasedQuantity = $product['purchased_quantity'];
            
                // Check if there's enough stock to update.
                if ($purchasedQuantity <= $availableQuantity) {
                    // Update the quantity of the selected stock product.
                    $newQuantity = $availableQuantity - $purchasedQuantity;
                    $stockProduct->update(['quantity' => $newQuantity]);

                    DB::table('destock')->insert([
                        'patient_movments_details_id' => $patientMovementsDetailsId,
                    ]);
            
                    $data = [
                        "message" => __("Produit déstocké avec succès."),
                    ];
            
                    return response()->json($data, 200);
                } else {
                    $data = [
                        "message" => __("Déstockage impossible pour raisons de stock insuffisant."),
                    ];
            
                    return response()->json($data, 400);
                }
            } else {
                $data = [
                    "message" => __("Produit non disponible."),
                ];
            
                return response()->json($data, 404);
            }
        } else {
            $data = [
                "message" => __("Ce produit a déjà été récupéré."),
            ];
        
            return response()->json($data, 404);
        }
    }

    public function getMedicalActDetailsForMovment($movmentId)
    {
        $medicalActDetails = DB::table('patient_movement_details')
            ->join('medical_acts', 'patient_movement_details.medical_acts_id', '=', 'medical_acts.id')
            ->join('movments', 'patient_movement_details.movments_id', '=', 'movments.id')
            ->join('patients', 'movments.patients_id', '=', 'patients.id')
            ->select('medical_acts.id', 'medical_acts.code', 'medical_acts.designation', 'medical_acts.price', 'patient_movement_details.medical_acts_qte', 'patient_movement_details.medical_acts_price', 'movments.id as movments_id')
            ->where('patient_movement_details.paid', 0)
            ->where('movments.id', $movmentId) // Filtrez par l'ID de la ligne sélectionnée
            ->get();

        return response()->json(['data' => $medicalActDetails]);
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
            ->where('movments.iep', $searchParam)
            ->first(); // Utilisez first() pour obtenir le premier résultat

        if (!$result) {
            return response()->json(['message' => 'Patient introuvable'], 404);
        }

        return response()->json(['data' => $result]);
    }





    // public function store(Request $request)
    // {
    //     $data = $request->all();

    //     // $user = $request->user();

    //     $annee = date('y');
    //     $mois = date('m');
    //     $jour = date('d');

    //     // Générer un code aléatoire de 8 chiffres
    //     $codeAleatoire = mt_rand(10000000, 99999999);

    //     // Concaténer les éléments pour former le code unique de la facture
    //     $codeUnique = $annee . $mois . $jour . '-' . $codeAleatoire;

    //     // Vérifier si le code généré est déjà utilisé (assurez-vous d'avoir une colonne unique pour le code dans la table)
    //     while (Facture::where('code', $codeUnique)->exists()) {
    //         // Générer un nouveau code aléatoire de 8 chiffres
    //         $codeAleatoire = mt_rand(10000000, 99999999);

    //         // Mettre à jour le code unique
    //         $codeUnique = $annee . $mois . $jour . '-' . $codeAleatoire;
    //     }



    //     $factures = $data['factures'];

    //     foreach ($factures as $factureData) {

    //         $validatedData = Validator::make($factureData, [
    //             'movments_id' => 'required',
    //             'mode_payements_id' => 'required',
    //             'acte_medical_id' => 'nullable|integer',
    //             // 'patient_id' => 'required|integer',
    //             // 'user_id' => 'required|exists:users,id', // Remplacez "users" par le nom de la table où vous souhaitez vérifier l'existence de 'user_id', et "id" par le nom de la colonne correspondante
    //             // 'centre_id' => 'required',
    //             'code' => 'nullable|string',
    //             'autre' => 'nullable|string',
    //             'prix' => 'required|integer',
    //             'quantite' => 'required|integer',
    //             'montant' => 'required|integer',
    //         ])->validate();

    //         $facture = Facture::create([

    //             'movments_id' => $validatedData['movments_id'],
    //             'mode_payements_id' => $validatedData['mode_payements_id'],
    //             'is_synced' => 0, // Marquer comme non synchronisé
    //             'reference' => $codeUnique,
    //             // 'payement_id' => $payementId,
    //             'acte_medical_id' => $validatedData['acte_medical_id'],
    //             // 'patient_id' => $validatedData['patient_id'],
    //             'user_id' => NULL,
    //             'centre_id' => NULL, // Récupérer l'id du centre à partir de la relation
    //             'code' => $validatedData['code'],
    //             'autre' => $validatedData['autre'],
    //             'prix' => $validatedData['prix'],
    //             'quantite' => $validatedData['quantite'],
    //             'montant' => $validatedData['montant'],
    //         ]);

    //         $factures[] = $facture;
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Factures enregistrées avec succès.',
    //         'data' => $factures,
    //     ], 201);
    // }


    public function store(Request $request)
    {

        $data = $request->all();
        $montantTotal = 0; // Initialisez le montant total à zéro

        $annee = date('y');
        $mois = date('m');
        $jour = date('d');

        $codeAleatoire = mt_rand(10000000, 99999999);
        $codeUnique = $annee . $mois . $jour . '-' . $codeAleatoire;

        while (Facture::where('code', $codeUnique)->exists()) {
            $codeAleatoire = mt_rand(10000000, 99999999);
            $codeUnique = $annee . $mois . $jour . '-' . $codeAleatoire;
        }



        $facturesData = $data['factures'];
        $factures = [];


        foreach ($facturesData as $factureData) {
            $validatedData = Validator::make($factureData, [
                'cash_registers_id' => 'required',
                'movments_id' => 'required',
                'mode_payements_id' => 'required',
                'acte_medical_id' => 'nullable|integer',
                'code' => 'nullable|string',
                'autre' => 'nullable|string',
                'prix' => 'required|integer',
                'quantite' => 'required|integer',
                'montant' => 'required|integer',
            ])->validate();

            $facture = Facture::create([
                // 'cash_registers_id' => $validatedData['cash_registers_id'],
                'cash_registers_id' => $validatedData['cash_registers_id'],
                'movments_id' => $validatedData['movments_id'],
                'mode_payements_id' => $validatedData['mode_payements_id'],
                'is_synced' => 0,
                'reference' => $codeUnique,
                'acte_medical_id' => $validatedData['acte_medical_id'],
                'user_id' => 1,
                'centre_id' => null,
                'code' => $validatedData['code'],
                'autre' => null,
                'prix' => $validatedData['prix'],
                'quantite' => $validatedData['quantite'],
                'montant' => $validatedData['montant'],
            ]);

            $montantTotal += $validatedData['montant'];

            if ($validatedData['acte_medical_id'] !== null) {
                DB::table('patient_movement_details')
                    ->where('medical_acts_id', $validatedData['acte_medical_id'])
                    ->update(['paid' => 1]);
            }


            $factures[] = $facture;
        }

        $cashRegister = CashRegister::find($validatedData['cash_registers_id']);
    

        if ($validatedData['mode_payements_id'] != 8) {
            // Mettez à jour le solde dans la table cash_registers
            // $cashRegister = CashRegister::find($validatedData['cash_registers_id']);
            $cashRegister->solde += $montantTotal;
            $cashRegister->save();
        } elseif ($validatedData['mode_payements_id'] == 8) {
            // Mettez à jour le montant dans la table credits
            // $cashRegister = CashRegister::find($validatedData['cash_registers_id']);
            $cashRegister->credits += $montantTotal;
            $cashRegister->save();
        }
        
        
        return response()->json([
            'success' => true,
            'message' => 'Factures enregistrées avec succès.',
            'data' => $factures,
            'reference' => $codeUnique,
            // 'statutCash' => $statut,
        ], 201);
    }


    public function getSolde($caisseID)
    {
        $solde = Facture::getSolde($caisseID);
        return response()->json(['data' => $solde]);
    }

    public function index()
    {
        $factures = DB::table('factures')
            ->select(
                'factures.reference',
                'factures.movments_id',
                'factures.mode_payements_id',
                'factures.created_at',
                DB::raw('SUM(factures.montant) as montant_total'),
                // Ajoutez d'autres colonnes non agrégées ici
                'patients.firstname',
                'patients.lastname'
            )
            ->join('movments', 'movments.id', '=', 'factures.movments_id')
            ->join('patients', 'patients.id', '=', 'movments.patients_id')
            ->groupBy('factures.reference', 'factures.movments_id', 'factures.mode_payements_id', 'factures.created_at', 'patients.firstname', 'patients.lastname')
            ->orderBy('factures.created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $factures,
            'message' => 'Liste des factures récupérées avec succès.'
        ]);
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
                'medical_acts.designation',
                'factures.quantite',
                'factures.montant',
                'factures.created_at'
            )
            ->join('movments', 'movments.id', '=', 'factures.movments_id')
            ->join('patients', 'patients.id', '=', 'movments.patients_id')
            ->join('medical_acts', 'medical_acts.id', '=', 'factures.acte_medical_id')
            ->where('factures.reference', $reference)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Détails des factures récupérés avec succès.',
            'data' => $factures,
        ], 200);
    }
}
