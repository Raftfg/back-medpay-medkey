<?php

namespace Modules\Hospitalization\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Hospitalization\Entities\Room;
use Modules\Administration\Entities\Service;
use Modules\Acl\Entities\User;
use Illuminate\Support\Str;

class RoomTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        // Récupérer un utilisateur (sans hospital_id car on est déjà dans la base tenant)
        $user = User::first();
        
        if (!$user) {
            $this->command->warn("⚠️  Aucun utilisateur trouvé. Veuillez d'abord exécuter UserTableSeeder.");
            return;
        }

        $roomsData = [
            [
                'code' => 'CH-001',
                'name' => 'Chambre Simple',
                'bed_capacity' => 1,
                'price' => 5000,
                'description' => 'Chambre simple avec un lit',
            ],
            [
                'code' => 'CH-002',
                'name' => 'Chambre Double',
                'bed_capacity' => 2,
                'price' => 8000,
                'description' => 'Chambre double avec deux lits',
            ],
            [
                'code' => 'CH-003',
                'name' => 'Chambre VIP',
                'bed_capacity' => 1,
                'price' => 15000,
                'description' => 'Chambre VIP avec équipements modernes',
            ],
            [
                'code' => 'CH-004',
                'name' => 'Chambre Standard',
                'bed_capacity' => 2,
                'price' => 6000,
                'description' => 'Chambre standard avec deux lits',
            ],
            [
                'code' => 'CH-005',
                'name' => 'Chambre Communautaire',
                'bed_capacity' => 4,
                'price' => 3000,
                'description' => 'Chambre communautaire avec quatre lits',
            ],
        ];

        // Récupérer les services pour associer chaque chambre à un service
        $services = Service::all();
        if ($services->isEmpty()) {
            $this->command->warn("⚠️  Aucun service trouvé. Les chambres seront créées sans services_id.");
        }
        $serviceCount = $services->count();
        $roomIndex = 0;

        foreach ($roomsData as $roomData) {
            // Répartition simple des chambres par service (round-robin)
            $serviceId = null;
            if ($serviceCount > 0) {
                $service = $services[$roomIndex % $serviceCount];
                $serviceId = $service ? $service->id : null;
            }

            Room::updateOrCreate(
                [
                    'code' => $roomData['code'],
                ],
                array_merge($roomData, [
                    'uuid' => Str::uuid(),
                'services_id' => $serviceId,
                    'user_id' => $user->id,
                    'is_synced' => false,
                ])
            );

            $roomIndex++;
        }

        $this->command->info('✅ Chambres créées.');
    }
}
