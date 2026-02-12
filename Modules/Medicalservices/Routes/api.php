<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Modules\Medicalservices\Http\Controllers\MedicalservicesController;

use Modules\Medicalservices\Http\Controllers\ChirurgieRecordController;
use Modules\Medicalservices\Http\Controllers\ConsultationRecordController;
use Modules\Medicalservices\Http\Controllers\ImagerieRecordController;
use Modules\Medicalservices\Http\Controllers\InfirmerieRecordController;
use Modules\Medicalservices\Http\Controllers\LaboratoireRecordController;
use Modules\Medicalservices\Http\Controllers\MaterniteRecordController;
use Modules\Medicalservices\Http\Controllers\PediatrieRecordController;
use Modules\Medicalservices\Http\Controllers\UrgencesRecordController;


/*header('Access-Control-Allow-Headers: Origin, Content-Type');
header('Content-Type': 'application/json');*/

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

$apiVersion = 'v' . config('premier.api_version');

Route::group(['prefix' => $apiVersion], function () {

    Route::group(['middleware' => ['auth:api']], function () {

        Route::apiResource('chirurgie_records', ChirurgieRecordController::class);
        Route::apiResource('consultation_records', ConsultationRecordController::class);
        Route::apiResource('imagerie_records', ImagerieRecordController::class);
        Route::apiResource('laboratoire_records', LaboratoireRecordController::class);

        Route::apiResource('maternite_records', MaterniteRecordController::class);
        Route::get('maternite/record', [MaterniteRecordController::class, 'getRecord']);

        Route::apiResource('pediatrie_records', PediatrieRecordController::class);
        Route::get('pediatrie/record', [PediatrieRecordController::class, 'getRecord']);

        /**Uergensces URL **/
        Route::apiResource('urgences_records', UrgencesRecordController::class);
        Route::get('urgences/categories',  [UrgencesRecordController::class, 'getUrgencesCategories']);
        Route::get('urgences/gravites',  [UrgencesRecordController::class, 'getUrgencesGravities']);
        Route::get('urgences/record', [UrgencesRecordController::class, 'getRecord']);


        Route::apiResource('infirmerie_records', InfirmerieRecordController::class);
        Route::get('infirmerie/record', [InfirmerieRecordController::class, 'getRecord']);


        Route::get('actes-by-services/{service_id}', [MedicalservicesController::class, 'getActesByServicesCode']);
        Route::get('services-by-code/{service_code}', [MedicalservicesController::class, 'getServiceByCode']);
    });
});
