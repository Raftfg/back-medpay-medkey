<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;


use Modules\Stock\Http\Controllers\ProductController;

use Modules\User\Http\Controllers\Api\V1\UserController;
use Modules\Movment\Http\Controllers\Api\V1\MovmentController;
use Modules\Payment\Http\Controllers\Api\V1\FactureController;
use Modules\Cash\Http\Controllers\Api\V1\CashRegisterController;
use Modules\Payment\Http\Controllers\Api\V1\OperationController;
use Modules\Payment\Http\Controllers\Api\V1\SignataireController;

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

// Route::middleware('auth:api')->get('/payment', function (Request $request) {
//     return $request->user();
// });

// Route::apiResource('users', UserController::class);
// Route::apiResource('movments', MovmentController::class);
// Route::apiResource('cash_registers', CashRegisterController::class);
// Route::apiResource('operations', OperationController::class);


// Route::group(['prefix' => 'api'], function () { //CL - Pour garder le même prefixe que les autres routes
//     $apiVersion = 'v' . config('premier.api_version');
//     Route::group(['prefix' => $apiVersion], function () {  //CL - Pour garder le même prefixe que les autres routes


//         Route::apiResource('operations', OperationController::class);

//         Route::group(['middleware' => ['auth:api']], function () {
//             //D'ici vers le haut ne change pas
//             //************************Begin : Module Payment**********************************

//             //************************End : Module Payment**********************************
//             //D'ici vers le bas ne change pas
//         });
//     });
// });


Route::group(['prefix' => 'api'], function () { //CL - Pour garder le même prefixe que les autres routes
    $apiVersion = 'v' . config('premier.api_version');
    Route::group(['prefix' => $apiVersion], function () {  //CL - Pour garder le même prefixe que les autres routes

        Route::group(['middleware' => ['auth:api']], function () {
            // Sécurisé: Endpoint déplacé dans le groupe auth:api pour éviter les fuites de données
            Route::get('/getbillsbydate/{date}', [FactureController::class, 'getBillsByDate']);
            //D'ici vers le haut ne change pas
            //************************Begin : Module Absence**********************************

            Route::get('/getbillsbycashier', [FactureController::class, 'getBillsByCashier']);

            Route::apiResource('operations', OperationController::class);

            Route::apiResource('signataires', SignataireController::class);
            Route::get('/users/titre/{uuid}', [SignataireController::class, 'getRoleOrTitle']);
            Route::get('/get-solde/{caisseID}', [FactureController::class, 'getSolde']);


            Route::get('/getactsmovment/{movmentsId}', [FactureController::class, 'getMedicalActDetailsForMovment']);
            Route::get('/getlistproductbyreference/{reference}', [FactureController::class, 'getListProductByreference']);

            Route::get('/listbillsbymovment/{movmentsId}', [FactureController::class, 'listBillsByMovment']);

            Route::get('/getact/{id}', [FactureController::class, 'getActe']);
            Route::get('/patientmovment/{movementId}', [FactureController::class, 'getPatientInfo']);


            Route::get('/patient/{ipp}/insurance-details', [FactureController::class, 'getInsuranceDetailsByIpp']);
            Route::get('/listmovment', [FactureController::class, 'listMovment']);
            Route::post('/search-movments', [FactureController::class, 'searchMovments']);
            Route::post('/mtn', [FactureController::class, 'mtn']);
            Route::get('/getpostpdf', [FactureController::class, 'getPostPdf']);

            Route::get('/getdetailfacture/{reference}', [FactureController::class, 'getDetailFacture']);

            Route::get('/generatecashreport/{cashRegisterID}/{date}', [FactureController::class, 'generateCashReport']);


            Route::get('/getstatusbyreference/{reference}', [FactureController::class, 'getStatusByReference']);

            Route::get('/payementfacture/{cashRegister}/{reference}/{mode_payements_id}/{partial_amount?}', [FactureController::class, 'payementFacture']);

            Route::get('/reportbillsperiod', [FactureController::class, 'reportBillsPeriod']);

            Route::get('/getdailystatistics', [FactureController::class, 'getDailyStatistics']);

            // Route::get('/getfacture', [FactureController::class, 'getPostPdf']);

            Route::get('/getinsurancedetailsbyIpp/{ipp}', [FactureController::class, 'getInsuranceDetailsByIpp']);

            Route::get('/getproductformatted/{id}', [FactureController::class, 'getProductFormatted']);
            Route::get('/getproductbyreference/{reference}', [FactureController::class, 'getProductByReference']);

            Route::post('/listbillsforsaleproduct', [FactureController::class, 'listBillsForsaleProduct']);

            Route::get('kkiapay/{transaction_id}', [FactureController::class, 'kkiapay']);

            Route::get('formatletter/{number}', [FactureController::class, 'formatLetter']);



            Route::get('/getbillsimpaye', [FactureController::class, 'getBillsImpaye']);

            Route::apiResource('factures', FactureController::class);



            //************************End : Module Absence**********************************
            //D'ici vers le bas ne change pas
        });
    });
});
