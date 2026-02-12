<?php

namespace Modules\Absence\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Absence\Entities\Absent;
use Modules\Absence\Entities\TypeVacation;
use App\Core\Models\Hospital;
use Modules\Acl\Entities\User;
use Illuminate\Support\Str;

class AbsentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        // Récupérer les utilisateurs et types de congés (sans hospital_id car on est déjà dans la base tenant)
        $users = User::limit(3)->get();
        $typeVacations = TypeVacation::all();
        
        if ($users->isEmpty() || $typeVacations->isEmpty()) {
            $this->command->warn("⚠️  Données de référence manquantes. Veuillez d'abord exécuter UserTableSeeder et TypeVacationSeeder.");
            return;
        }

        // Créer quelques absences de test
        foreach ($users as $index => $user) {
            if ($index < $typeVacations->count()) {
                Absent::updateOrCreate(
                    [
                        'users_id' => $user->id,
                        'start_date' => now()->subDays(5),
                    ],
                    [
                        'users_id' => $user->id,
                        'vacations_id' => $typeVacations[$index]->id,
                        'start_date' => now()->subDays(5),
                        'end_date' => now()->addDays(2),
                        'uuid' => Str::uuid(),
                        'is_synced' => false,
                    ]
                );
            }
        }

        $this->command->info('✅ Absences créées.');
    }
}
