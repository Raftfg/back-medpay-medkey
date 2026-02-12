<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;


use Modules\User\Http\Controllers\Api\V1\UserController;


use Modules\Cash\Http\Controllers\Api\V1\CashRegisterController;
use Modules\Cash\Http\Controllers\Api\V1\AllocateCashController;
use Modules\Cash\Http\Controllers\Api\V1\HistoricalOpenCloseController;
use Modules\Cash\Http\Controllers\Api\V1\CashRegisterTransfertController;


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
            //D'ici vers le haut ne change pas
            //************************Begin : Module Absence**********************************

            
        Route::apiResource('cashregister', CashRegisterController::class);

        Route::apiResource('cashregistertransfert', CashRegisterTransfertController::class);
        Route::put('addfund/{uuid}/{amount}', [CashRegisterController::class, 'addFund']);
        Route::get('bordereautransfert/{idCashRegister}', [CashRegisterController::class, 'bordereauTransfert']);

        
        // Route::post('/search-movments', [FactureController::class, 'searchMovments']);
        // Route::post('/affectcashier', [AllocateCashController::class, 'store']);
        Route::apiResource('affectcashier', AllocateCashController::class);
        Route::get('getcashierscashregister/{cashRegisterID}', [AllocateCashController::class, 'getCashiersCashRegister']); 
        Route::get('getuserbyid/{id}', [AllocateCashController::class, 'getUserByID']); 

        Route::get('/getcashiers', [AllocateCashController::class, 'getCahiers']); // liste des caissiers
        Route::post('/openclosecashregister', [HistoricalOpenCloseController::class, 'store']);
        Route::get('getcashregistercashier/{id}', [AllocateCashController::class, 'getCashRegisterCashier']);
        Route::put('choosecashregister/{cashRegisterID}/{cashierID}', [AllocateCashController::class, 'chooseCashRegister']);
        Route::get('getcashregistercashiercurrent/{cashierID}', [AllocateCashController::class, 'getCashRegisterCashierCurrent']);
        
        Route::get('gethistoricalcurrent/{cashRegisterID}', [AllocateCashController::class, 'getHistoricalCurrent']);


        Route::get('gethistoriqueopen', [HistoricalOpenCloseController::class, 'getHistoriqueOpen']);
        Route::get('gethistoriqueclose', [HistoricalOpenCloseController::class, 'getHistoriqueClose']);
        
        Route::get('getapprover', [CashRegisterTransfertController::class, 'getApprover']);

        

            //************************End : Module Absence**********************************
            //D'ici vers le bas ne change pas
        });
    });
});
