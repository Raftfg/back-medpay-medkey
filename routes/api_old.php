<?php

/*use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayementController;
// use App\Http\Controllers\PaiementController;

use App\Http\Controllers\PermissionController;
use App\Http\Controllers\TerminalAffectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\MTNController;
use App\Http\Controllers\TypeCentreController;

use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ActMedicalController;
use App\Http\Controllers\QuartierController;
use App\Http\Controllers\ArrondissementController;
use App\Http\Controllers\CommuneController;
use App\Http\Controllers\PaysController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssuranceController;
use App\Http\Controllers\IndigenceController;
use App\Http\Controllers\IndigencePatientController;
use App\Http\Controllers\PriseEnChargePatientController;
use App\Http\Controllers\CentreSanitaireController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\ModePaiementController;
use App\Http\Controllers\EncaissementController;
use App\Http\Controllers\DecaissementController;

use App\Http\Controllers\CompagnieController;
use App\Http\Controllers\ModePayementController;
use App\Http\Controllers\TerminalController;
// use Illuminate\Auth\Middleware\Authenticate as Middleware;
use SimpleSoftwareIO\QrCode\Facades\QrCode;*/
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// Route::get('/rapport/users', [FactureController::class, 'getFacturesToday']); // liste des factures de ce jour et la list des caissiers de l'hopital



/*Route::post('auth/login', [AuthController::class, 'login']);

Route::post('/update-transaction-amount', [TransactionController::class, 'updateTransactionAmount'])->name('updateTransactionAmount');



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/smspayer_meth/{payementId}', [PayementController::class, 'mtn']);

Route::post('/especepayement/{payementId}', [PayementController::class, 'especePayer']);

// Route::post('/payement', [PayementController::class, 'store']);

Route::post('/fedapay/{payementId}', [PayementController::class, 'fedapay']);


Route::get('/smspayer_meth', [PayementController::class, 'mtn']);

// Route::get('/auth/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('auth/reset-password', [AuthController::class, 'reset']);
Route::post('auth/request-password', [AuthController::class, 'requestPassword']);
Route::post('auth/register', [AuthController::class, 'register']);

Route::get('/users/centre', [UserController::class, 'getCurrentUserCentre']);
Route::post('/payment', [MTNController::class, 'makePayment'])->name('payment');



Route::middleware(['auth:sanctum'])->group(function () {

    Route::apiResource('users', UserController::class);


    Route::get('/rapport/terminal', [FactureController::class, 'getFacturesTerminalToday']); // liste des factures de ce jour et la list des caissiers de l'hopital
    Route::get('/rapport/users', [FactureController::class, 'getFacturesToday']); // liste des factures de ce jour et la list des caissiers de l'hopital

    Route::post('/facture/{payementId}', [FactureController::class, 'store']); // sauvegarder une facture dans la base de données

    Route::get('/rapport/{terminalId}', [FactureController::class, 'getFactureTerminal']); // list des facture en fonction du caissier choisi
    Route::get('/rapport/caissier/{caissierId}', [FactureController::class, 'getUserCentre']); // list des facture en fonction du caissier choisi

    Route::get('/caissiers', [UserController::class, 'getCaissierByCenter']); // list des facture en fonction du caissier choisi


    Route::get('/rapporthopital/{hopital}', [FactureController::class, 'getUserCentr']);
    Route::get('/rapportdeparte/{departe}', [FactureController::class, 'getUserDeparte']);
    Route::get('/rapportcom/{com}', [FactureController::class, 'getUserCom']);
    Route::get('/rapportaron/{aron}', [FactureController::class, 'getUserAron']);
    Route::get('/rapportquart/{quart}', [FactureController::class, 'getUserQua']);
    Route::get('/rapportgeneral', [FactureController::class, 'rapportGeneral']);

    Route::get('/showfacture/{id}', [FactureController::class, 'showFacture']);

    Route::post('/storefacture', [FactureController::class, 'storeFacture']);

    Route::post('/storeuseronline', [UserController::class, 'storeUserOnline']);
    Route::put('/updateuseronline', [UserController::class, 'updateUserOnline']);


    

    Route::apiResource('facture', FactureController::class);
    // Route::apiResource('actmedical', ActMedicalController::class);

    Route::post('/users/changeprofile', [UserController::class, 'updateProfile']);
    Route::get('/user/profile', [UserController::class, 'showProfile']);
    Route::post('/users/changepassword', [UserController::class, 'updatePassProfil']);
    // Route::apiResource('users', UserController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::post('droitusers', [RoleController::class, 'droitUsers']);
    Route::put('auth/update-password', [AuthController::class, 'updatePassword']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('droitusers', [RoleController::class, 'droitUsers']);

    Route::apiResource('typecentre', TypeCentreController::class);
    Route::apiResource('terminals', TerminalController::class);
    Route::apiResource('affectterminals', TerminalAffectController::class);


    Route::apiResource('patients', PatientController::class);

    Route::put('patients', [PatientController::class, 'update']);
    Route::get('patientts/{id}/payment', [PatientController::class, 'getPaymentsByPatient']);


    Route::get('/roles/{roleId}/permissions', [RoleController::class, 'getPermissionsForRole']);

    Route::apiResource('roles', RoleController::class);

    // Route::get('roles', RoleController::class);
    Route::apiResource('pays', PaysController::class);
    Route::apiResource('actmedical', ActMedicalController::class);

    Route::apiResource('assurances', AssuranceController::class);
    // Route::apiResource('paiements', PaiementController::class);
    Route::apiResource('indigencespatients', IndigencePatientController::class);


    Route::apiResource('indigences', IndigenceController::class);

    Route::get('pourcentagepatient/{id}', [IndigencePatientController::class, 'getPercentageByAge']);
    Route::get('pourcentagepa/{id}', [IndigencePatientController::class, 'getPercentageBy']);





    Route::apiResource('compagnies', CompagnieController::class);
    Route::get('compagniess/{compagnieId}/assurances', [CompagnieController::class, 'getAssuranceByCompagnie']);


    // wadjid
    Route::get('/communes', [CommuneController::class, 'getCommunesByDepartement']);
    Route::get('/arrondissements', [ArrondissementController::class, 'getArrondissementsByCommune']);
    Route::get('/quartiers', [QuartierController::class, 'getQuartiersByArrondissement']);
    Route::apiResource('quartier', QuartierController::class);
    Route::apiResource('arrondissement', ArrondissementController::class);
    Route::apiResource('commune', CommuneController::class);
    Route::apiResource('departement', DepartementController::class);
    Route::apiResource('modepayement', ModePayementController::class);
    Route::apiResource('centresanitaire', CentreSanitaireController::class);
    Route::apiResource('modepaiement', ModePaiementController::class);

    Route::apiResource('payement', PayementController::class);





    Route::apiResource('priseencharges', PriseEnChargePatientController::class);


    // Route::get('/encaissement-uuid/{uuid}', [EncaissementController::class, 'showuid']);


    Route::post('/storedecaissement', [DecaissementController::class, 'storeDecaissement']);



    Route::apiResource('encaissement', EncaissementController::class);
    Route::post('/decaissement', [DecaissementController::class, 'store']);
    Route::get('/decaissement', [DecaissementController::class, 'index']);
    Route::post('/decaissement/{id}', [DecaissementController::class, 'update']);
    Route::post('/decaissement/updatefile/{id}', [DecaissementController::class, 'updateFile']);
    Route::get('/decaissement/{id}', [DecaissementController::class, 'show']);

    Route::put('/decaissement/{id}', [DecaissementController::class, 'UpdateOnlinDecaissement']);

    
    // Route::get('/decaissement/{id}/facture', [DecaissementController::class, 'showFile']);





    Route::get('/assuran', [AssuranceController::class, 'getAssurancesByCompagnie']);
    Route::get('/assuran/{assuranceId}/pourcentage', [AssuranceController::class, 'getPourcentageByAssurance']);

    Route::get('getassurancepatient/{idpatient}', [PriseEnChargePatientController::class, 'getAssurancePatient']);
    Route::get('kkiapay/{transaction_id}', [PayementController::class, 'kkiapay']);


    Route::get('/factures/codeqr/{id}', [FactureController::class, 'generateQrCode'])->name('factures.codeqr'); // lien pour generer le code QR


    /****New function by Christ **/

  /*  Route::get('/recette', [FactureController::class, 'getTotalMontants']);
    Route::get('/patientcount', [PatientController::class, 'countPatients']);
    Route::get('/listfact', [FactureController::class, 'getRecentFactures']);
    Route::get('/getDecaissement', [DecaissementController::class, 'getTotaldecaisse']);
    Route::get('/getgrapheDecaissement', [DecaissementController::class, 'getMontantParMois']);
    Route::get('/getgrapheEncaissement', [EncaissementController::class, 'getMontantParMois']);
    Route::post('/rapports/filtrer', [FactureController::class, 'filtrer']);
    
});




Route::get('/factures/codeqr/{id}', [FactureController::class, 'generateQrCode'])->name('factures.codeqr'); // lien pour generer le code QR


Route::get('/factures/especes', [FactureController::class, 'getAllEspece']);

Route::get('/espece/{id}', [FactureController::class, 'getEspece']);

Route::get('/factures/momos', [FactureController::class, 'getAllMomos']);
Route::get('/factures/fedapay', [FactureController::class, 'getAllFedapay']);
Route::get('/decaissement/{id}/facture', [DecaissementController::class, 'showFile']);


// Route::get('/facture/{ref}',  [FactureController::class, 'codeQR']);
// FFFFFFFFFFFFFF
