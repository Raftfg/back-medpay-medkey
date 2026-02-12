<?php
use Illuminate\Support\Facades\Route;
use Modules\Stock\Http\Controllers\Api\V1\StoreController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('stock')->group(function() {
    // Route::get('/', 'StockController@index');
});
Route::get('/store/{uuid}/stocks', [StoreController::class, 'getStocks']);


