<?php

namespace Modules\Rendezvous\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Modules\Rendezvous\Entities\Appointment;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::query()
            ->with(['patient', 'doctor', 'service'])
            ->orderBy('scheduled_at', 'asc');

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->integer('doctor_id'));
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->integer('service_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date('date'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $appointments = $query->paginate(50);

        return reponse_json_transform([
            'message' => __("Liste des rendez-vous."),
            'data' => $appointments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'nullable|integer',
            'doctor_id' => 'required|integer',
            'service_id' => 'nullable|integer',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:5|max:480',
            'type' => 'nullable|string|max:100',
            'source' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $duration = $validated['duration_minutes'] ?? config('rendezvous.default_slot_duration', 30);

        return DB::connection('tenant')->transaction(function () use ($validated, $duration) {
            $scheduledAt = \Carbon\Carbon::parse($validated['scheduled_at']);
            $endAt = (clone $scheduledAt)->addMinutes($duration);

            // Vérifier le chevauchement (créneau libre)
            $overlap = Appointment::where('doctor_id', $validated['doctor_id'])
                ->where('status', '!=', 'cancelled')
                ->where(function ($q) use ($scheduledAt, $endAt) {
                    $q->whereBetween('scheduled_at', [$scheduledAt, $endAt])
                        ->orWhereBetween(DB::raw("DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE)"), [$scheduledAt, $endAt])
                        ->orWhere(function ($q2) use ($scheduledAt, $endAt) {
                            $q2->where('scheduled_at', '<=', $scheduledAt)
                                ->where(DB::raw("DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE)"), '>=', $endAt);
                        });
                })
                ->exists();

            if ($overlap) {
                return reponse_json_transform([
                    'message' => __("Le créneau est déjà réservé pour ce médecin."),
                ], 422);
            }

            $appointment = Appointment::create([
                'uuid' => (string) Str::uuid(),
                'patient_id' => $validated['patient_id'] ?? null,
                'doctor_id' => $validated['doctor_id'],
                'service_id' => $validated['service_id'] ?? null,
                'scheduled_at' => $scheduledAt,
                'duration_minutes' => $duration,
                'type' => $validated['type'] ?? 'consultation',
                'status' => 'confirmed',
                'source' => $validated['source'] ?? 'on_site',
                'notes' => $validated['notes'] ?? null,
            ]);

            $appointment->load(['patient', 'doctor', 'service']);

            return reponse_json_transform([
                'message' => __("Rendez-vous créé avec succès."),
                'data' => $appointment,
            ], 201);
        });
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor', 'service']);

        return reponse_json_transform([
            'message' => __("Détails du rendez-vous."),
            'data' => $appointment,
        ]);
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'scheduled_at' => 'sometimes|date',
            'duration_minutes' => 'sometimes|integer|min:5|max:480',
            'type' => 'sometimes|string|max:100',
            'status' => 'sometimes|string|in:pending,confirmed,cancelled,done,no_show',
            'notes' => 'nullable|string',
        ]);

        return DB::connection('tenant')->transaction(function () use ($validated, $appointment) {
            if (isset($validated['scheduled_at']) || isset($validated['duration_minutes'])) {
                $scheduledAt = isset($validated['scheduled_at'])
                    ? \Carbon\Carbon::parse($validated['scheduled_at'])
                    : $appointment->scheduled_at;

                $duration = $validated['duration_minutes'] ?? $appointment->duration_minutes;
                $endAt = (clone $scheduledAt)->addMinutes($duration);

                $overlap = Appointment::where('doctor_id', $appointment->doctor_id)
                    ->where('id', '!=', $appointment->id)
                    ->where('status', '!=', 'cancelled')
                    ->where(function ($q) use ($scheduledAt, $endAt) {
                        $q->whereBetween('scheduled_at', [$scheduledAt, $endAt])
                            ->orWhereBetween(DB::raw("DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE)"), [$scheduledAt, $endAt])
                            ->orWhere(function ($q2) use ($scheduledAt, $endAt) {
                                $q2->where('scheduled_at', '<=', $scheduledAt)
                                    ->where(DB::raw("DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE)"), '>=', $endAt);
                            });
                    })
                    ->exists();

                if ($overlap) {
                    return reponse_json_transform([
                        'message' => __("Le nouveau créneau chevauche un autre rendez-vous."),
                    ], 422);
                }
            }

            $appointment->fill($validated);
            $appointment->save();

            $appointment->load(['patient', 'doctor', 'service']);

            return reponse_json_transform([
                'message' => __("Rendez-vous mis à jour avec succès."),
                'data' => $appointment,
            ]);
        });
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $appointment->status = 'cancelled';
        $appointment->cancellation_reason = $validated['reason'] ?? null;
        $appointment->save();

        return reponse_json_transform([
            'message' => __("Rendez-vous annulé avec succès."),
            'data' => $appointment->fresh(),
        ]);
    }

    /**
     * Envoi (simulé) des rappels pour un rendez-vous donné.
     * Dans une version ultérieure, ceci pourra être délégué à des Jobs queue + SMS/Email réels.
     */
    public function sendReminders(Appointment $appointment)
    {
        $now = now();

        if (!$appointment->reminder_sent_at) {
            $appointment->reminder_sent_at = $now;
        } elseif (!$appointment->second_reminder_sent_at) {
            $appointment->second_reminder_sent_at = $now;
        }

        $appointment->save();

        return reponse_json_transform([
            'message' => __("Rappels marqués comme envoyés pour ce rendez-vous."),
            'data' => $appointment->fresh(),
        ]);
    }
}

