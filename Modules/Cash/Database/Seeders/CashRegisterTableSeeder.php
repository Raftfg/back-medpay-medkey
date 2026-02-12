<?php

namespace Modules\Cash\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Cash\Entities\CashRegister;
use App\Core\Models\Hospital;
use Modules\Acl\Entities\User;
use Illuminate\Support\Str;

class CashRegisterTableSeeder extends Seeder
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

        $cashRegistersData = [
            [
                'designation' => 'Caisse Principale',
                'description' => 'Caisse principale de l\'hôpital',
                'type' => 'A', // Actes médicaux
            ],
            [
                'designation' => 'Caisse Pharmacie',
                'description' => 'Caisse pour les ventes de produits pharmaceutiques',
                'type' => 'P', // Pharmacie
            ],
        ];

        foreach ($cashRegistersData as $cashRegisterData) {
            CashRegister::updateOrCreate(
                [
                    'designation' => $cashRegisterData['designation'],
                ],
                array_merge($cashRegisterData, [
                    'uuid' => Str::uuid(),
                    'user_id' => $user->id,
                    'solde' => 0,
                    'total_partial' => 0,
                    'credits' => 0,
                    'is_synced' => false,
                ])
            );
        }

        $this->command->info('✅ Caisses créées.');
    }
}
