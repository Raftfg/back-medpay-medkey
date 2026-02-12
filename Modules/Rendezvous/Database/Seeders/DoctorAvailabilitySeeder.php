<?php

namespace Modules\Rendezvous\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Modules\Acl\Entities\Role;
use Modules\Acl\Entities\User;
use Modules\Administration\Entities\Service;
use Modules\Rendezvous\Entities\DoctorAvailability;

class DoctorAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant

        // Rechercher le rôle "médecin" avec plusieurs variantes possibles
        $roleNames = ['Doctor', 'Médecin', 'Docteur', 'medecin'];
        $role = null;

        foreach ($roleNames as $roleName) {
            $role = Role::where('name', $roleName)
                ->where('guard_name', 'api')
                ->first();

            if ($role) {
                break;
            }
        }

        if (!$role) {
            Log::warning('[Rendezvous] Aucun rôle médecin trouvé pour générer les disponibilités.');
            return;
        }

        $doctors = User::whereHas('roles', function ($query) use ($role) {
            $query->where('roles.id', $role->id)
                ->where('roles.guard_name', 'api');
        })->get();

        if ($doctors->isEmpty()) {
            Log::info('[Rendezvous] Aucun utilisateur avec le rôle médecin trouvé pour générer les disponibilités.');
            return;
        }

        // Récupérer les services disponibles pour associer les médecins à un service
        $services = Service::all();
        if ($services->isEmpty()) {
            Log::warning('[Rendezvous] Aucun service trouvé. Les disponibilités seront créées sans service_id.');
        }

        // Création de disponibilités simples : du lundi au vendredi, 08h-12h et 14h-18h
        $doctorIndex = 0;
        foreach ($doctors as $doctor) {
            // Ne pas dupliquer si des disponibilités existent déjà
            if (DoctorAvailability::where('doctor_id', $doctor->id)->exists()) {
                continue;
            }

            // Choisir un service pour ce médecin (répartition simple en round-robin)
            $serviceId = null;
            if ($services->count() > 0) {
                $service = $services[$doctorIndex % $services->count()];
                $serviceId = $service ? $service->id : null;
            }

            $daysOfWeek = [1, 2, 3, 4, 5]; // 1 = Lundi, ..., 5 = Vendredi
            foreach ($daysOfWeek as $day) {
                DoctorAvailability::create([
                    'doctor_id' => $doctor->id,
                    'service_id' => $serviceId,
                    'day_of_week' => $day,
                    'start_time' => '08:00',
                    'end_time' => '12:00',
                    'slot_duration_minutes' => config('rendezvous.default_slot_duration', 30),
                    'is_active' => true,
                ]);

                DoctorAvailability::create([
                    'doctor_id' => $doctor->id,
                    'service_id' => $serviceId,
                    'day_of_week' => $day,
                    'start_time' => '14:00',
                    'end_time' => '18:00',
                    'slot_duration_minutes' => config('rendezvous.default_slot_duration', 30),
                    'is_active' => true,
                ]);
            }

            Log::info('[Rendezvous] Disponibilités créées pour le médecin', [
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->name . ' ' . $doctor->prenom,
                'service_id' => $serviceId,
            ]);

            $doctorIndex++;
        }
    }
}

