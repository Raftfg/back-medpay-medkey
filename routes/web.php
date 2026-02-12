<?php

use App\Http\Controllers\PayementController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
//use App\Http\Controllers\TransactionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
 */

Route::get('/', function () {
    return view('welcome');
});


// Route::post('/update-transaction-amount', [TransactionController::class, 'updateTransactionAmount'])->name('updateTransactionAmount');





// Route::get('/', [PayementController::class, 'index']);
// Route::post('/smspayer_meth', [PayementController::class, 'mtn']);
// Route::get('/', [PayementController::class, 'index']);
// Route::post('/smspayer_meth', [PayementController::class, 'mtn']);
