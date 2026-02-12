<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Planning\Http\Controllers\Api\V1\WorkScheduleController;

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

// IMPORTANT: le préfixe 'api' est déjà appliqué par le RouteServiceProvider du module
$apiVersion = 'v' . config('premier.api_version');
Route::group(['prefix' => $apiVersion], function () {
    Route::apiResource('work-schedules', WorkScheduleController::class);
    Route::post('/work-schedules/{uuid}/publish', [WorkScheduleController::class, 'publish'])->name('work-schedules.publish');
});
