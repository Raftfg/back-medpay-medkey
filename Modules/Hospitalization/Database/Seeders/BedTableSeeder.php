<?php

namespace Modules\Hospitalization\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Hospitalization\Entities\Bed;
use Modules\Hospitalization\Entities\Room;
use App\Core\Models\Hospital;
use Modules\Acl\Entities\User;
use Illuminate\Support\Str;

class BedTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        // Récupérer les chambres (sans hospital_id car on est déjà dans la base tenant)
        $rooms = Room::all();
        
        if ($rooms->isEmpty()) {
            $this->command->warn("⚠️  Aucune chambre trouvée. Veuillez d'abord exécuter RoomTableSeeder.");
            return;
        }

        $user = User::first();
        
        foreach ($rooms as $room) {
            // Créer les lits selon la capacité de la chambre
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
                        'user_id' => $user ? $user->id : null,
                        'is_synced' => false,
                    ]
                );
            }
        }

        $this->command->info('✅ Lits créés.');
    }
}
