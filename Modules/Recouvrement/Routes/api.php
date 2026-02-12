<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Recouvrement\Http\Controllers\RecouvrementController;


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


// Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
//     Route::get('recouvrement', fn (Request $request) => $request->user())->name('recouvrement');
//   // Route::apiResource('recouvrement', RecouvrementController::class);
// });

Route::group(['prefix' => 'api'], function () { //CL - Pour garder le même prefixe que les autres routes
    $apiVersion = 'v' . config('premier.api_version');
    Route::group(['prefix' => $apiVersion], function () {  

        //CL - Pour garder le même prefixe que les autres routes

        Route::group(['middleware' => ['auth:api']], function () {
            Route::post('addRecouvrement/{reference}', [RecouvrementController::class, 'store']);
            Route::get('/recouvrements', [RecouvrementController::class, 'index'])->name('recouvrements.index');
            // Route::get('recouvrement', fn (Request $request) => $request->user())->name('recouvrement');
            // Route::apiResource('recouvrement', Recouvrement::class);

            //************************End : Module Recouvrement**********************************
            //D'ici vers le bas ne change pas
        });
    });
});

