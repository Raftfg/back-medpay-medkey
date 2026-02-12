<?php

namespace Modules\Hospitalization\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Acl\Entities\Role;
use Modules\Acl\Entities\User;
use Modules\Administration\Entities\Service;
use Modules\Hospitalization\Entities\Bed;
use Modules\Hospitalization\Entities\Room;
use Modules\Rendezvous\Entities\DoctorAvailability;

class ServiceDoctorsAndBedsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Ce seeder associe des médecins et des lits à chaque service.
     * - Pour chaque service, il assigne des médecins via DoctorAvailability
     * - Pour chaque service, il assigne des chambres (et crée des lits si nécessaire)
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('🌱 Début du seeding : Association médecins et lits aux services');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // 1. Récupérer tous les services
        $services = Service::all();
        if ($services->isEmpty()) {
            $this->command->warn('⚠️  Aucun service trouvé. Veuillez d\'abord exécuter ServiceTableSeeder.');
            return;
        }

        $this->command->info("📋 Nombre de services trouvés : {$services->count()}");

        // 2. Récupérer les médecins
        $roleNames = ['Doctor', 'Médecin', 'Docteur', 'medecin', 'docteur'];
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
            $this->command->warn('⚠️  Aucun rôle médecin trouvé. Création des disponibilités ignorée.');
            $doctors = collect([]);
        } else {
            $doctors = User::whereHas('roles', function ($query) use ($role) {
                $query->where('roles.id', $role->id)
                    ->where('roles.guard_name', 'api');
            })->get();

            $this->command->info("👨‍⚕️ Nombre de médecins trouvés : {$doctors->count()}");
        }

        // 3. Vérifier si la colonne services_id existe dans la table rooms
        $hasServicesIdColumn = Schema::connection('tenant')->hasColumn('rooms', 'services_id');
        if (!$hasServicesIdColumn) {
            $this->command->warn('⚠️  La colonne services_id n\'existe pas dans la table rooms.');
            $this->command->warn('⚠️  Veuillez exécuter la migration : php artisan tenant:migrate {hospital_id}');
            $this->command->warn('⚠️  Ou la migration spécifique : 2026_01_26_010223_add_services_id_to_rooms_table');
        }

        // 4. Récupérer les chambres existantes
        $rooms = Room::all();
        $this->command->info("🏥 Nombre de chambres trouvées : {$rooms->count()}");

        // 5. Récupérer un utilisateur pour les créations
        $user = User::first();
        if (!$user) {
            $this->command->warn('⚠️  Aucun utilisateur trouvé. Certaines créations peuvent échouer.');
        }

        $totalDoctorsAssigned = 0;
        $totalBedsAssigned = 0;
        $totalRoomsAssigned = 0;

        // 6. Pour chaque service, assigner des médecins et des lits
        foreach ($services as $index => $service) {
            $this->command->line('');
            $this->command->info("📌 Service : {$service->name} (ID: {$service->id})");

            // 5.1. Assigner des médecins au service
            if ($doctors->isNotEmpty()) {
                // Calculer combien de médecins assigner par service (minimum 1, maximum 3)
                $doctorsPerService = max(1, min(3, (int)ceil($doctors->count() / $services->count())));
                
                // Sélectionner les médecins pour ce service (round-robin)
                $serviceDoctors = $doctors->slice($index * $doctorsPerService, $doctorsPerService);
                
                if ($serviceDoctors->isEmpty()) {
                    // Si on a épuisé les médecins, réutiliser les premiers
                    $serviceDoctors = $doctors->take($doctorsPerService);
                }

                $doctorsAssigned = 0;
                foreach ($serviceDoctors as $doctor) {
                    // Vérifier si une disponibilité existe déjà pour ce médecin et ce service
                    $existing = DoctorAvailability::where('doctor_id', $doctor->id)
                        ->where('service_id', $service->id)
                        ->first();

                    if (!$existing) {
                        // Créer des disponibilités pour ce médecin dans ce service
                        // Du lundi au vendredi, 08h-12h et 14h-18h
                        $daysOfWeek = [1, 2, 3, 4, 5]; // 1 = Lundi, ..., 5 = Vendredi
                        
                        foreach ($daysOfWeek as $day) {
                            // Matin : 08h-12h
                            DoctorAvailability::create([
                                'doctor_id' => $doctor->id,
                                'service_id' => $service->id,
                                'day_of_week' => $day,
                                'start_time' => '08:00',
                                'end_time' => '12:00',
                                'slot_duration_minutes' => 30,
                                'is_active' => true,
                            ]);

                            // Après-midi : 14h-18h
                            DoctorAvailability::create([
                                'doctor_id' => $doctor->id,
                                'service_id' => $service->id,
                                'day_of_week' => $day,
                                'start_time' => '14:00',
                                'end_time' => '18:00',
                                'slot_duration_minutes' => 30,
                                'is_active' => true,
                            ]);
                        }

                        $doctorsAssigned++;
                        $totalDoctorsAssigned++;
                    }
                }

                $this->command->line("   ✅ {$doctorsAssigned} médecin(s) assigné(s) au service");
            } else {
                $this->command->line("   ⚠️  Aucun médecin disponible pour assignation");
            }

            // 5.2. Assigner des chambres au service
            if ($rooms->isNotEmpty()) {
                // Calculer combien de chambres assigner par service (minimum 1, maximum 3)
                $roomsPerService = max(1, min(3, (int)ceil($rooms->count() / $services->count())));
                
                // Sélectionner les chambres pour ce service (round-robin)
                $serviceRooms = $rooms->slice($index * $roomsPerService, $roomsPerService);
                
                if ($serviceRooms->isEmpty()) {
                    // Si on a épuisé les chambres, réutiliser les premières
                    $serviceRooms = $rooms->take($roomsPerService);
                }

                $roomsAssigned = 0;
                $bedsCreated = 0;

                foreach ($serviceRooms as $room) {
                    // Mettre à jour le service_id de la chambre si elle n'en a pas déjà un
                    // Vérifier d'abord si la colonne existe
                    if ($hasServicesIdColumn) {
                        if ($room->services_id !== $service->id) {
                            $room->update(['services_id' => $service->id]);
                            $roomsAssigned++;
                            $totalRoomsAssigned++;
                        }
                    } else {
                        // Si la colonne n'existe pas, on ne peut pas assigner de service
                        $this->command->line("   ⚠️  Impossible d'assigner le service (colonne services_id manquante)");
                    }

                    // Vérifier si la chambre a des lits
                    $existingBeds = Bed::where('room_id', $room->id)->count();
                    
                    if ($existingBeds === 0) {
                        // Créer des lits pour cette chambre selon sa capacité
                        for ($i = 1; $i <= $room->bed_capacity; $i++) {
                            Bed::updateOrCreate(
                                [
                                    'room_id' => $room->id,
                                    'code' => $room->code . '-LIT-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                                ],
                                [
                                    'room_id' => $room->id,
                                    'code' => $room->code . '-LIT-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                                    'name' => 'Lit ' . $i . ' - ' . $room->name,
                                    'uuid' => Str::uuid(),
                                    'state' => 'free',
                                    'user_id' => $user ? $user->id : null,
                                ]
                            );
                            $bedsCreated++;
                            $totalBedsAssigned++;
                        }
                    } else {
                        // Compter les lits existants pour le total
                        $totalBedsAssigned += $existingBeds;
                    }
                }

                $this->command->line("   ✅ {$roomsAssigned} chambre(s) assignée(s) au service");
                if ($bedsCreated > 0) {
                    $this->command->line("   ✅ {$bedsCreated} lit(s) créé(s) pour ce service");
                }
            } else {
                $this->command->line("   ⚠️  Aucune chambre disponible. Création de chambres et lits...");
                
                // Vérifier si on peut créer des chambres avec services_id
                if (!$hasServicesIdColumn) {
                    $this->command->warn("   ❌ Impossible de créer des chambres : colonne services_id manquante");
                    $this->command->warn("   💡 Exécutez : php artisan tenant:migrate {hospital_id}");
                } else {
                    // Créer des chambres et lits pour ce service
                    $roomsCreated = $this->createRoomsAndBedsForService($service, $user, $index, $hasServicesIdColumn);
                    $totalRoomsAssigned += $roomsCreated['rooms'];
                    $totalBedsAssigned += $roomsCreated['beds'];
                    
                    $this->command->line("   ✅ {$roomsCreated['rooms']} chambre(s) et {$roomsCreated['beds']} lit(s) créé(s)");
                }
            }
        }

        // 6. Résumé
        $this->command->line('');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('✅ Seeding terminé avec succès !');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info("📊 Résumé :");
        $this->command->info("   - Services traités : {$services->count()}");
        $this->command->info("   - Médecins assignés : {$totalDoctorsAssigned}");
        $this->command->info("   - Chambres assignées : {$totalRoomsAssigned}");
        $this->command->info("   - Lits créés/assignés : {$totalBedsAssigned}");
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }

    /**
     * Crée des chambres et des lits pour un service donné
     *
     * @param Service $service
     * @param User|null $user
     * @param int $index Index du service pour la génération de codes
     * @param bool $hasServicesIdColumn Indique si la colonne services_id existe
     * @return array ['rooms' => int, 'beds' => int]
     */
    private function createRoomsAndBedsForService(Service $service, $user, int $index, bool $hasServicesIdColumn = true): array
    {
        $roomsCreated = 0;
        $bedsCreated = 0;

        // Créer 2-3 chambres par service
        $roomsToCreate = 2 + ($index % 2); // 2 ou 3 chambres

        for ($r = 1; $r <= $roomsToCreate; $r++) {
            $roomCode = $service->code . '-CH-' . str_pad($r, 2, '0', STR_PAD_LEFT);
            $bedCapacity = 2; // 2 lits par chambre par défaut

            $roomData = [
                'code' => $roomCode,
                'name' => 'Chambre ' . $r . ' - ' . $service->name,
                'bed_capacity' => $bedCapacity,
                'price' => 5000 + ($r * 1000), // Prix variable
                'description' => 'Chambre assignée au service ' . $service->name,
                'uuid' => Str::uuid(),
                'user_id' => $user ? $user->id : null,
            ];
            
            // Ajouter services_id seulement si la colonne existe
            if ($hasServicesIdColumn) {
                $roomData['services_id'] = $service->id;
            }
            
            $room = Room::updateOrCreate(
                [
                    'code' => $roomCode,
                ],
                $roomData
            );

            $roomsCreated++;

            // Créer les lits pour cette chambre
            for ($b = 1; $b <= $bedCapacity; $b++) {
                Bed::updateOrCreate(
                    [
                        'room_id' => $room->id,
                        'code' => $roomCode . '-LIT-' . str_pad($b, 2, '0', STR_PAD_LEFT),
                    ],
                    [
                        'room_id' => $room->id,
                        'code' => $roomCode . '-LIT-' . str_pad($b, 2, '0', STR_PAD_LEFT),
                        'name' => 'Lit ' . $b . ' - ' . $room->name,
                        'uuid' => Str::uuid(),
                        'state' => 'free',
                        'user_id' => $user ? $user->id : null,
                    ]
                );
                $bedsCreated++;
            }
        }

        return [
            'rooms' => $roomsCreated,
            'beds' => $bedsCreated,
        ];
    }
}
