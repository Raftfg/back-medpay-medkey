<?php

namespace Modules\Rendezvous\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Modules\Rendezvous\Entities\Appointment;
use Modules\Acl\Entities\User;
use Modules\Patient\Entities\Patiente;
use Modules\Administration\Entities\Service;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant

        $doctors = User::query()
            ->select('id', 'name', 'prenom')
            ->get();

        if ($doctors->isEmpty()) {
            Log::info('[Rendezvous] Aucun utilisateur trouvé pour générer des rendez-vous.');
            return;
        }

        $patients = Patiente::query()
            ->select('id', 'lastname', 'firstname')
            ->limit(20)
            ->get();

        if ($patients->isEmpty()) {
            Log::info('[Rendezvous] Aucun patient trouvé pour générer des rendez-vous.');
            return;
        }

        $service = Service::query()->select('id')->first();

        $today = Carbon::today();

        foreach ($doctors as $doctor) {
            // Générer quelques rendez-vous sur les 5 prochains jours
            for ($dayOffset = 0; $dayOffset < 5; $dayOffset++) {
                $date = $today->copy()->addDays($dayOffset);

                // Trois rendez-vous par jour : 09:00, 10:00, 11:00
                $hours = [9, 10, 11];

                foreach ($hours as $hour) {
                    $scheduledAt = $date->copy()->setTime($hour, 0, 0);

                    // Choisir un patient au hasard
                    $patient = $patients->random();

                    // Éviter les doublons
                    $exists = Appointment::where('doctor_id', $doctor->id)
                        ->where('patient_id', $patient->id)
                        ->where('scheduled_at', $scheduledAt)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    Appointment::create([
                        'uuid' => (string) Str::uuid(),
                        'patient_id' => $patient->id,
                        'doctor_id' => $doctor->id,
                        'service_id' => $service ? $service->id : null,
                        'scheduled_at' => $scheduledAt,
                        'duration_minutes' => config('rendezvous.default_slot_duration', 30),
                        'type' => 'consultation',
                        'status' => 'confirmed',
                        'source' => 'on_site',
                        'notes' => 'Rendez-vous généré automatiquement pour les tests.',
                    ]);
                }
            }

            Log::info('[Rendezvous] Rendez-vous de test créés pour le médecin', [
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->name . ' ' . $doctor->prenom,
            ]);
        }
    }
}

