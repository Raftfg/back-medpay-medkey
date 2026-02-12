<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Absence\Http\Controllers\Api\V1\AbsentController;
use Modules\Absence\Http\Controllers\Api\V1\MissionController;
use Modules\Absence\Http\Controllers\Api\V1\VacationController;
use Modules\Absence\Http\Controllers\Api\V1\TypeVacationController;
use Modules\Absence\Http\Controllers\Api\V1\MissionParticipantController;

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

// $middlewareBase = tenants_middleware();
// Route::group(['middleware' => $middlewareBase,], function () use ($middlewareBase) {
    Route::group(['prefix' => 'api'], function () { //CL - Pour garder le même prefixe que les autres routes
        $apiVersion = 'v' . config('premier.api_version');
        Route::group(['prefix' => $apiVersion], function () {  //CL - Pour garder le même prefixe que les autres routes
            Route::apiResource('absences', AbsentController::class);
            
            Route::group(['middleware' => ['auth:api']], function () {
                //D'ici vers le haut ne change pas
                //************************Begin : Module Absence**********************************

                Route::apiResource('missions', MissionController::class);
                Route::apiResource('mission_participants', MissionParticipantController::class);
                
                Route::apiResource('type_vacations', TypeVacationController::class);
                Route::apiResource('vacations', VacationController::class);
                // Validation manager/RH
                Route::post('vacations/{uuid}/approve', [VacationController::class, 'approve']);
                Route::post('vacations/{uuid}/reject', [VacationController::class, 'reject']);

                //************************End : Module Absence**********************************
                //D'ici vers le bas ne change pas
            });
        });
    });
// });
