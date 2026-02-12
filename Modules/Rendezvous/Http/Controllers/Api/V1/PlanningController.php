<?php

namespace Modules\Rendezvous\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Modules\Rendezvous\Entities\Appointment;
use Modules\Rendezvous\Entities\DoctorAvailability;
use Modules\Acl\Entities\User;

class PlanningController extends Controller
{
    /**
     * Retourne les créneaux disponibles pour un médecin / service / date donnée.
     */
    public function availableSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|integer',
            'service_id' => 'nullable|integer',
            'date' => 'required|date',
        ]);

        $doctorId = $request->integer('doctor_id');
        $serviceId = $request->integer('service_id') ?: null;
        $date = Carbon::parse($request->get('date'))->startOfDay();
        $dayOfWeek = (int) $date->dayOfWeek; // 0-6

        $availabilities = DoctorAvailability::query()
            ->where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->where('day_of_week', $dayOfWeek)
            ->when($serviceId, fn($q) => $q->where('service_id', $serviceId))
            ->get();

        if ($availabilities->isEmpty()) {
            return reponse_json_transform([
                'message' => __("Aucune disponibilité configurée pour ce médecin ce jour-là."),
                'data' => [],
            ]);
        }

        $slots = [];

        foreach ($availabilities as $availability) {
            $slotDuration = $availability->slot_duration_minutes ?? config('rendezvous.default_slot_duration', 30);

            $start = (clone $date)->setTimeFromTimeString($availability->start_time);
            $end = (clone $date)->setTimeFromTimeString($availability->end_time);

            while ($start->lt($end)) {
                $slotEnd = (clone $start)->addMinutes($slotDuration);
                if ($slotEnd->gt($end)) {
                    break;
                }

                $hasAppointment = Appointment::where('doctor_id', $doctorId)
                    ->whereDate('scheduled_at', $date->toDateString())
                    ->where('status', '!=', 'cancelled')
                    ->where(function ($q) use ($start, $slotEnd) {
                        $q->whereBetween('scheduled_at', [$start, $slotEnd])
                            ->orWhereBetween(\DB::raw("DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE)"), [$start, $slotEnd])
                            ->orWhere(function ($q2) use ($start, $slotEnd) {
                                $q2->where('scheduled_at', '<=', $start)
                                    ->where(\DB::raw("DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE)"), '>=', $slotEnd);
                            });
                    })
                    ->exists();

                $slots[] = [
                    'start' => $start->toDateTimeString(),
                    'end' => $slotEnd->toDateTimeString(),
                    'available' => !$hasAppointment,
                ];

                $start->addMinutes($slotDuration);
            }
        }

        return reponse_json_transform([
            'message' => __("Créneaux disponibles calculés."),
            'data' => $slots,
        ]);
    }

    /**
     * Planning d'un médecin sur une période (par défaut jour en cours).
     */
    public function doctorPlanning(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|integer',
            'date' => 'nullable|date',
        ]);

        $doctorId = $request->integer('doctor_id');
        $date = $request->filled('date') ? Carbon::parse($request->get('date')) : Carbon::today();

        $appointments = Appointment::with(['patient', 'service'])
            ->where('doctor_id', $doctorId)
            ->whereDate('scheduled_at', $date->toDateString())
            ->orderBy('scheduled_at')
            ->get();

        return reponse_json_transform([
            'message' => __("Planning du médecin pour la journée."),
            'data' => [
                'date' => $date->toDateString(),
                'appointments' => $appointments,
            ],
        ]);
    }

    /**
     * Retourne la liste des médecins ayant des disponibilités pour un service donné.
     */
    public function doctorsByService(Request $request)
    {
        $request->validate([
            'service_id' => 'required|integer',
        ]);

        $serviceId = $request->integer('service_id');

        // Récupérer les disponibilités liées à ce service et charger les médecins associés
        $availabilities = DoctorAvailability::with('doctor')
            ->where('service_id', $serviceId)
            ->where('is_active', true)
            ->get();

        // Extraire les médecins uniques
        $doctors = $availabilities
            ->pluck('doctor')
            ->filter() // retirer les nulls
            ->unique('id')
            ->values();

        return reponse_json_transform([
            'message' => __("Liste des médecins pour ce service."),
            'data' => $doctors,
        ]);
    }
}

