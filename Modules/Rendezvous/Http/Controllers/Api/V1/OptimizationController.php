<?php

namespace Modules\Rendezvous\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Modules\Rendezvous\Entities\Appointment;

class OptimizationController extends Controller
{
    /**
     * Fournit des suggestions d'optimisation de planning basées sur des heuristiques simples.
     * (Point d'entrée pour de futurs modèles IA plus avancés.)
     */
    public function suggestOptimizations(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $doctorId = $request->integer('doctor_id');
        $startDate = $request->filled('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::today()->subWeek();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::today()->addWeek();

        $appointments = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('scheduled_at', [$startDate, $endDate])
            ->get();

        // Heuristique simple : calcul de la durée moyenne et des jours/horaires de forte demande
        $byHour = [];
        $totalDuration = 0;
        $count = 0;

        foreach ($appointments as $appointment) {
            $start = Carbon::parse($appointment->scheduled_at);
            $hour = $start->format('H:00');
            $dayName = $start->locale('fr_FR')->dayName;

            $key = $dayName . ' ' . $hour;
            $byHour[$key] = ($byHour[$key] ?? 0) + 1;

            $totalDuration += $appointment->duration_minutes;
            $count++;
        }

        arsort($byHour);

        $suggestions = [
            'optimal_slot_duration' => $count > 0 ? round($totalDuration / $count) : config('rendezvous.default_slot_duration', 30),
            'peak_periods' => array_slice($byHour, 0, 5, true),
        ];

        return reponse_json_transform([
            'message' => __("Suggestions d'optimisation générées."),
            'data' => $suggestions,
        ]);
    }
}

