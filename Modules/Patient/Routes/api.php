<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Patient\Http\Controllers\Api\V1\PatienteController;
use Modules\Patient\Http\Controllers\Api\V1\PatientInsuranceController;

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
// Le préfixe 'api' est déjà ajouté par RouteServiceProvider, donc on ne l'ajoute pas ici
$apiVersion = 'v' . config('premier.api_version');
Route::group(['prefix' => $apiVersion], function () {

       
        // Route::apiResource('patient_insurances', PatientInsuranceController::class);
        Route::group(['middleware' => ['auth:api']], function () {
            //D'ici vers le haut ne change pas
            //************************Begin : Module Patient**********************************
            Route::apiResource('patient_insurances', PatientInsuranceController::class);
            Route::post('/patient_insurances/add', [PatientInsuranceController::class, 'add']);
            Route::get('/patients/search/{request}', [PatienteController::class, 'search']);
            Route::get('/patients/count', [PatienteController::class, 'countPatients']);
            Route::get('/patients/{uuid}/packs', [PatientInsuranceController::class, 'getPacksByPatient'])->name('patients.packs');
            Route::get('/patientinsurance/patient/{id}', [PatientInsuranceController::class, 'getInsuranceByPatient']);
            Route::get('/patient-insurances/{uuid}/pack-details', [PatientInsuranceController::class, 'getPackDetailsByPatient']);
            Route::apiResource('patients', PatienteController::class);
            Route::get('/detailpatient/{uuid}', [PatienteController::class, 'detailpatient']);
            // Route::apiResource('patients', PatienteController::class);
            // Route::apiResource('patient_insurances', PatientInsuranceController::class);

            //************************End : Module Patient**********************************
            //D'ici vers le bas ne change pas
        });
    });
// });