<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Remboursement\Http\Controllers\RemboursementController;
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
// Route::get('remboursement', fn (Request $request) => $request->user())->name('remboursement');
Route::group(['prefix' => 'api'], function () { //CL - Pour garder le même prefixe que les autres routes
    $apiVersion = 'v' . config('premier.api_version');
    Route::group(['prefix' => $apiVersion], function () {


        Route::group(['middleware' => ['auth:api']], function () {
            Route::get('/list-remboursements', [RemboursementController::class, 'listRemboursements']);

            Route::get('/eligible-patients', [RemboursementController::class, 'showEligiblePatients']);

            Route::get('/factures-remboursees', [RemboursementController::class, 'getFacturesRemboursees']);

            Route::get('/generate-facture/{factureId}', [RemboursementController::class, 'generateFactureWithCaissierName']);

            Route::get('/get-patient-name/{movmentsId}', [RemboursementController::class, 'getPatientName']);
            Route::patch('/update-percentage/{invoiceId}/{percentage}', [RemboursementController::class, 'updatePercentage']);
            Route::put('/update-facture/{factureId}', [RemboursementController::class, 'updateFactureRemboursement']);

            Route::get('/remboursement/{invoiceReference}', [RemboursementController::class, 'getFacturesPayeesNonDestockees']);
            // Route::get('/get-refunded-invoices/{patientId}/{invoiceReference}/{startDate}/{endDate}', [RemboursementController::class, 'getRefundedInvoices']);
            Route::get('/get-refunded-invoices/{invoiceReference}/{startDate}/{endDate}', [RemboursementController::class, 'getRefundedInvoices']);

            // Dans routes/api.php

            Route::get('/payment-details/{patientId}', [RemboursementController::class, 'getPaymentDetails']);

            Route::post('/process-refund/{patientId}', [RemboursementController::class, 'processRefund']);
            // Route::get('recouvrement', fn (Request $request) => $request->user())->name('recouvrement');
            // Route::apiResource('recouvrement', Recouvrement::class);

            //************************End : Module Recouvrement**********************************
            //D'ici vers le bas ne change pas
        });
    });
});
// });
