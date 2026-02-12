<?php


use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;
use Modules\Patient\Entities\Patiente;

use Modules\Movment\Http\Controllers\MovmentController;
use Modules\Movment\Http\Controllers\Api\V1\AdmissionController;
use Modules\Movment\Http\Controllers\Api\V1\DmeController;
use Modules\Movment\Http\Controllers\Api\V1\AntecedentController as ApiAntecedentController;
use Modules\Movment\Http\Controllers\Api\V1\AllergieController as ApiAllergieController;
use Modules\Movment\Http\Controllers\Api\V1\ClinicalObservationController;
use Modules\Movment\Http\Controllers\Api\V1\VaccinationController;
use Modules\Movment\Http\Controllers\AntecedentController;
use Modules\Movment\Http\Controllers\AllergieController;
use Modules\Movment\Http\Controllers\LivestyleController;
use Modules\Movment\Http\Controllers\MeasurementController;
use Modules\Movment\Http\Controllers\ReportController;

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
    Route::get('/movments/patients', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => Patiente::all(),
            'message' => 'Liste des patients.'
        ]);
    });

    // Routes explicites pour éviter les conflits de route model binding
    Route::get('movments', [MovmentController::class, 'index'])->name('movments.index');
    Route::post('movments', [MovmentController::class, 'store'])->name('movments.store');
    Route::get('movments/{movment}', [MovmentController::class, 'show'])->name('movments.show');
    Route::put('movments/{movment}', [MovmentController::class, 'update'])->name('movments.update');
    Route::patch('movments/{movment}', [MovmentController::class, 'update'])->name('movments.update');
    Route::delete('movments/{movment}', [MovmentController::class, 'destroy'])->name('movments.destroy');
    Route::post('movments/admit', [AdmissionController::class, 'admit']);
    Route::post('movments/transfer', [AdmissionController::class, 'transfer']);
    Route::post('movments/release', [AdmissionController::class, 'release']);

    Route::get('get-all/movments', [MovmentController::class, 'getAll']);
    Route::get('consultation/movments', [MovmentController::class, 'getConsultationMovments']);


    Route::get('movments/services/{service_id}', [MovmentController::class, 'getMovmentsByService']);
    Route::get('movments/actes/{movment_id}', [MovmentController::class,'getMovmentActes']);
    Route::get('movments/products/{movment_id}', [MovmentController::class,'getMovmentProducts']);
    Route::get('movments/check-getout', [MovmentController::class,'checkGetout']);

    Route::post('movments/actes/store', [MovmentController::class,'storeActe']);
    Route::post('movments/products/store', [MovmentController::class,'storeProduct']);
    Route::post('movments/actes/delete', [MovmentController::class,'deleteActe']);


    Route::apiResource('measurements', MeasurementController::class);
    Route::post('measurements/delete', [MeasurementController::class,'destroy']);

    Route::apiResource('livestyles', LivestyleController::class);
    Route::post('livestyles/delete', [LivestyleController::class,'destroy']);

    // Routes DME - API V1 (nouvelles routes complètes)
    Route::prefix('dme')->group(function () {
        Route::apiResource('antecedents', ApiAntecedentController::class);
        Route::apiResource('allergies', ApiAllergieController::class);
        Route::apiResource('observations', ClinicalObservationController::class);
        Route::apiResource('vaccinations', VaccinationController::class);
        Route::apiResource('prescriptions', \Modules\Movment\Http\Controllers\Api\V1\PrescriptionController::class);
        
        // Route download doit être définie AVANT apiResource pour éviter les conflits
        Route::get('documents/{id}/download', [\Modules\Movment\Http\Controllers\Api\V1\DmeDocumentController::class, 'download'])->name('dme.documents.download');
        Route::apiResource('documents', \Modules\Movment\Http\Controllers\Api\V1\DmeDocumentController::class);
    });

    // Routes legacy (maintenues pour compatibilité)
    Route::apiResource('allergies', AllergieController::class);
    Route::post('allergies/delete', [AllergieController::class,'destroy']);

    Route::apiResource('antecedents', AntecedentController::class);
    Route::post('antecedents/delete', [AntecedentController::class,'destroy']);

    Route::post('movments/updateOut', [MovmentController::class,'updateOut']);
    Route::get('movments/all', [MovmentController::class, 'getAll']);

    /*Recordes ***/
    Route::get('get-records', [MovmentController::class,'getRecord']);
    Route::post('switch-services', [MovmentController::class,'switchServices']);

    Route::post('movments/records/consultation', [MovmentController::class,'recordConsultation']);

    Route::get('report/patients/statics', [ReportController::class,'getPatientSats']);
    Route::get('report/services/statics', [ReportController::class,'getServicesSats']);

    /* DME Routes */
    Route::get('dme/full/{patient_uuid}', [DmeController::class, 'getFullDme']);
    Route::get('dme/ai-summary/{patient_uuid}', [DmeController::class, 'getAiSummary']);
    Route::get('dme/cim10/search', [DmeController::class, 'searchCim10']);
    
    Route::get('patients/medicals/records/{patient_uuid}', [MovmentController::class,'getPatientMedicalsRecords']);

});

});
