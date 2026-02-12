<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use Modules\Administration\Entities\ProductType;
use Modules\Patient\Http\Controllers\Api\V1\PatienteController;

// use Modules\Administration\Http\Controllers\Api\V1\PackController;
// use Modules\Administration\Http\Controllers\Api\V1\PaysController;
//  use Modules\Administration\Http\Controllers\AdministrationController;
// use Modules\Administration\Http\Controllers\Api\V1\CommuneController;
// use Modules\Administration\Http\Controllers\Api\V1\QuartierController;
// use Modules\Administration\Http\Controllers\Api\V1\InsuranceController;
// use Modules\Patient\Http\Controllers\Api\V1\PatientInsuranceController;
// use Modules\Administration\Http\Controllers\Api\V1\DepartementController;
// use Modules\Administration\Http\Controllers\Api\V1\ProductTypeController;
// use Modules\Administration\Http\Controllers\Api\V1\ArrondissementController;
use Modules\Administration\Http\Controllers\Api\V1\DepartmentController;
use Modules\Administration\Http\Controllers\Api\V1\ServiceController;


use Modules\Administration\Http\Controllers\Api\V1\PackController;
use Modules\Administration\Http\Controllers\Api\V1\PaysController;
use Modules\Administration\Http\Controllers\AdministrationController;
use Modules\Administration\Http\Controllers\Api\V1\CommuneController;
use Modules\Administration\Http\Controllers\Api\V1\QuartierController;
use Modules\Administration\Http\Controllers\Api\V1\InsuranceController;
use Modules\Patient\Http\Controllers\Api\V1\PatientInsuranceController;
use Modules\Administration\Http\Controllers\Api\V1\DepartementController;
use Modules\Administration\Http\Controllers\Api\V1\ProductTypeController;
use Modules\Administration\Http\Controllers\Api\V1\ArrondissementController;




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

/*Route::middleware('auth:api')->get('/administration', function (Request $request) {
    return $request->user();
});*/

// Le préfixe 'api' est déjà ajouté par le RouteServiceProvider du module
// Pas besoin de l'ajouter ici pour éviter le double préfixe api/api/v1
$apiVersion = 'v' . config('premier.api_version');
Route::group(['prefix' => $apiVersion], function () {

         Route::group(['middleware' => ['auth:api']], function () {
            Route::apiResource('departments', DepartmentController::class);
            Route::apiResource('services', ServiceController::class);
    
            // Route::get('/communes', [CommuneController::class, 'getCommunesByDepartement']);
            // Route::get('/arrondissements', [ArrondissementController::class, 'getArrondissementsByCommune']);
            // Route::get('/quartiers', [QuartierController::class, 'getQuartiersByArrondissement']);
            // Route::apiResource('packs', PackController::class);
            // Route::get('/pack', [PackController::class, 'getPackByInsurance']);
            // Route::apiResource('pays', PaysController::class);
            // Route::apiResource('departement', DepartementController::class);
            // Route::apiResource('commune', CommuneController::class);
            // Route::apiResource('arrondissement', ArrondissementController::class);
            // Route::apiResource('quartier', QuartierController::class);
            // Route::apiResource('productypes', ProductTypeController::class);
    
            Route::get('/communes', [CommuneController::class, 'getCommunesByDepartement']);
            Route::get('/arrondissements', [ArrondissementController::class, 'getArrondissementsByCommune']);
            Route::get('/quartiers', [QuartierController::class, 'getQuartiersByArrondissement']);
            Route::apiResource('packs', PackController::class);
            // Route::apiResource('packs', PackController::class);
            Route::get('/insurances/{insuranceId}/with-packs', [InsuranceController::class, 'getInsuranceWithPacks']);
            Route::get('/pack/{uuid}', [PackController::class, 'getPackByInsurance']);
            Route::get('/pack/{uuid}/insurance/packs', [PackController::class, 'getPacksOfInsuranceByPack']);
            Route::apiResource('pays', PaysController::class);
            Route::apiResource('departement', DepartementController::class);
            Route::apiResource('commune', CommuneController::class);
            Route::apiResource('arrondissement', ArrondissementController::class);
            Route::apiResource('quartier', QuartierController::class);
            Route::apiResource('productypes', ProductTypeController::class);
    

    
            Route::get('/administration/departments', [AdministrationController::class, 'getDepartments']);
            Route::get('/administration/departments/get/{department_id}', [AdministrationController::class, 'getServicesByDepartment']);
    
    
            Route::get('/administration/services', [AdministrationController::class, 'getServices']);
            Route::post('/administration/services', [AdministrationController::class, 'storeServices']);
            Route::post('/administration/services/delete', [AdministrationController::class, 'deleteServices']);
    
            Route::post('/administration/actes', [AdministrationController::class, 'storeActes']);
            Route::post('/administration/actes/delete', [AdministrationController::class, 'deleteActes']);
            Route::get('/administration/services/actes/{services_id}', [AdministrationController::class, 'getActesByServices']);
             Route::get('/administration/actes/lastcode/{services_id}', [AdministrationController::class, 'getLastActeCode']);
    
            Route::get('/administration/typesactes', [AdministrationController::class, 'getTypeMedicalActs']);
    
             Route::apiResource('insurances', InsuranceController::class);
             
             //D'ici vers le haut ne change pas
             //************************Begin : Module Patient**********************************
             

            // Route::apiResource('patients', PatienteController::class);
            // Route::apiResource('patient_insurances', PatientInsuranceController::class);


            //************************End : Module Patient**********************************
            //D'ici vers le bas ne change pas
            //
            //

         //************************End : Module Patient**********************************
            //D'ici vers le bas ne change pas



        });
    });

