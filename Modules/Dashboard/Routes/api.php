<?php

use Illuminate\Http\Request;
use Modules\Dashboard\Http\Controllers\Api\V1\DashboardController;

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
Route::group(['prefix' => $apiVersion], function () {  //CL - Pour garder le même prefixe que les autres routes

    //Module Dashboard
    Route::group(['middleware' => middleware_systeme_defaut()], function () {
        Route::get('dashboard', [DashboardController::class, 'index']);
   });
});

