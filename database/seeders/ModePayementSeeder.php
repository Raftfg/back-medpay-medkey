<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ModePayement;

class ModePayementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modes = [
            ['mode' => 'Espèce'],
            ['mode' => 'Carte crédit'],
            ['mode' => 'MOMO'],
            ['mode' => 'Flooz'],
            ['mode' => 'Carte Bancaire'],
        ];

        // Upsert pour éviter les doublons et assurer l'idempotence
        // Nécessite une contrainte unique sur la colonne `mode` si souhaité
        ModePayement::upsert($modes, ['mode'], ['mode']);
    }
}


