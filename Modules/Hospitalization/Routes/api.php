<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Hospitalization\Entities\Room;
use Modules\Hospitalization\Http\Controllers\Api\V1\BedController;
use Modules\Hospitalization\Http\Controllers\Api\V1\RoomController;
use Modules\Hospitalization\Http\Controllers\Api\V1\BedPatientController;

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

// Le préfixe 'api' est déjà ajouté par le RouteServiceProvider du module
// Pas besoin de l'ajouter ici pour éviter le double préfixe api/api/v1
$apiVersion = 'v' . config('premier.api_version');

Route::group(['prefix' => $apiVersion], function () {  //CL - Pour garder le même prefixe que les autres routes

    Route::group(['middleware' => ['auth:api']], function () {
        // Routes spécifiques AVANT apiResource pour éviter les conflits
        Route::get('/beds/available', [BedController::class, 'getAvailableBeds'])->name('beds.available');
        Route::get('/room/{uuid}/free-beds', [RoomController::class, 'getFreeBeds'])->name('rooms.free-beds');
        Route::get('/count-hospitalized-patients', [BedController::class, 'countCurrentlyHospitalizedPatients'])->name('beds.count-hospitalized');
        
        // Resources
        Route::apiResource('beds', BedController::class);
        Route::apiResource('rooms', RoomController::class);
        Route::apiResource('bed_patients', BedPatientController::class);
    });
});
