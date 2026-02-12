<?php

use Illuminate\Http\Request;
use Modules\Media\Http\Controllers\Api\V1\MediaController;

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

Route::group(['prefix' => 'api'], function () { //CL - Pour garder le même prefixe que les autres routes
    $apiVersion = 'v' . config('premier.api_version');
    Route::group(['prefix' => $apiVersion], function () {  //CL - Pour garder le même prefixe que les autres routes
        Route::group(['middleware' => middleware_systeme_defaut()], function () {

            //Module Etape
            Route::resource('medias', MediaController::class)->only(['destroy']);
        });
    });
});

