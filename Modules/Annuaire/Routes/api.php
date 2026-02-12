<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use Modules\Annuaire\Http\Controllers\Api\V1\ContratController;
use Modules\Annuaire\Http\Controllers\Api\V1\EmployerController;
use Modules\Annuaire\Http\Controllers\Api\V1\FormationController;
use Modules\Annuaire\Http\Controllers\Api\V1\CompetenceController;
use Modules\Annuaire\Http\Controllers\Api\V1\CertificationController;
use Modules\Annuaire\Http\Controllers\Api\V1\Experience_proController;
use Modules\Annuaire\Http\Controllers\Api\V1\EmployerPerformanceController;

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

            Route::apiResource('employers', EmployerController::class);
            Route::get('/employers/search/{request}', [EmployerController::class, 'search']);
            Route::apiResource('contrats', ContratController::class);


            Route::group(['middleware' => ['auth:api']], function () {
                //D'ici vers le haut ne change pas
                //************************Begin : Module Annuaire**********************************

                Route::apiResource('certifications', CertificationController::class);
                Route::apiResource('competences', CompetenceController::class);
                Route::apiResource('experience_pros', Experience_proController::class);
                Route::apiResource('formations', FormationController::class);
                Route::apiResource('employer-performances', EmployerPerformanceController::class);

                //************************End : Module Annuaire**********************************
                //D'ici vers le bas ne change pas
            });
        });
    });

// });
