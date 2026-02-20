<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');

Route::view('/ressources', 'resources')->name('resources');
Route::view('/faq', 'faq')->name('faq');
Route::view('/mentions-legales', 'legal.mentions')->name('legal.mentions');
Route::view('/confidentialite', 'legal.confidentialite')->name('legal.confidentialite');
Route::view('/conditions', 'legal.conditions')->name('legal.conditions');
Route::view('/cookies', 'legal.cookies')->name('legal.cookies');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
