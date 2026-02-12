<?php

use Illuminate\Support\Facades\Route;
use Modules\Rendezvous\Http\Controllers\Api\V1\AppointmentController;
use Modules\Rendezvous\Http\Controllers\Api\V1\PlanningController;
use Modules\Rendezvous\Http\Controllers\Api\V1\OptimizationController;

$apiVersion = 'v' . config('premier.api_version');

Route::group(['prefix' => $apiVersion, 'middleware' => ['auth:api']], function () {

    // Créneaux, planning et médecins par service (doivent être définis AVANT les routes avec {appointment} pour éviter le route model binding sur 'planning')
    Route::get('appointments/available-slots', [PlanningController::class, 'availableSlots']);
    Route::get('appointments/planning', [PlanningController::class, 'doctorPlanning']);
    Route::get('appointments/doctors-by-service', [PlanningController::class, 'doctorsByService']);
    Route::get('appointments/optimization/suggestions', [OptimizationController::class, 'suggestOptimizations']);

    // Rendez-vous : CRUD basique
    Route::get('appointments', [AppointmentController::class, 'index']);
    Route::post('appointments', [AppointmentController::class, 'store']);
    Route::get('appointments/{appointment}', [AppointmentController::class, 'show']);
    Route::put('appointments/{appointment}', [AppointmentController::class, 'update']);
    Route::delete('appointments/{appointment}', [AppointmentController::class, 'cancel']);

    // Rappels (doivent aussi rester après les routes "fixes")
    Route::post('appointments/{appointment}/send-reminders', [AppointmentController::class, 'sendReminders']);
});

