<?php

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Modules\Acl\Http\Controllers\Api\V1\AuthController;
use Modules\Acl\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\TenantRegistrationController;
use App\Http\Controllers\Api\V1\OnboardingWizardController;


// $apiVersion = 'v' . config('premier.api_version');
// Route::group(['prefix' => $apiVersion], function () {  //CL - Pour garder le même prefixe que les autres routes
//     require_once("premier/passport.php");

//         Route::post('login', [AuthController::class, 'login']);
//         Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot_password');
//         Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('auth.password_reset');

//         Route::get('email-confirmation/{uuid}', [AuthController::class, 'emailConfirmation'])->name('auth.email_confirmation');
   
// });
// ============================================
// ROUTES D'AUTHENTIFICATION
// ============================================
// Toutes les routes d'authentification ont été déplacées dans Modules/Acl/Routes/api.php
// pour centraliser la gestion de l'authentification dans le module ACL.
//
// Routes déplacées :
// - login
// - register
// - logout
// - user_current
// - email-confirmation
// - reset-password
// - request-password
//
// Voir : Modules/Acl/Routes/api.php
// ============================================

// Route::group(['prefix' => 'api'], function () { //CL - Pour garder le même prefixe que les autres routes
    $apiVersion = 'v' . config('premier.api_version');
    Route::group(['prefix' => $apiVersion], function () {  //CL - Pour garder le même prefixe que les autres routes

        // =====================================================
        //  PUBLIC TENANT ONBOARDING (sans middleware tenant)
        // =====================================================
        Route::post('public/tenants/register', [TenantRegistrationController::class, 'register']);
        Route::get('public/tenants/{uuid}/status', [TenantRegistrationController::class, 'status']);
        Route::post('public/tenants/autologin/consume', [TenantRegistrationController::class, 'consumeAutologinToken']);

        // Routes protégées (auth + tenant) pour le wizard
        Route::group(['middleware' => ['auth:api', 'tenant', 'ensure.tenant.connection']], function () {
            Route::get('onboarding/wizard', [OnboardingWizardController::class, 'getState']);
            Route::post('onboarding/wizard/hospital-info', [OnboardingWizardController::class, 'saveHospitalInfo']);
            Route::post('onboarding/wizard/language', [OnboardingWizardController::class, 'saveLanguage']);
            Route::post('onboarding/wizard/complete', [OnboardingWizardController::class, 'complete']);
        });

        // Route::apiResource('users', UserController::class); //create and edit sont exclus
        Route::group(['middleware' => ['auth:api']], function () {
            // Routes d'authentification déplacées dans Modules/Acl/Routes/api.php
            
            //D'ici vers le haut ne change pas
            //************************Begin : Module Annuaire**********************************
            //************************End : Module Annuaire**********************************
            //D'ici vers le bas ne change pas
        });
    });
// });

//Forcer le HTTPS
if (app()->environment() == "production") {
    URL::forceScheme('https');
}