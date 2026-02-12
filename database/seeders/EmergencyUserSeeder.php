<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Core\Models\Hospital;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmergencyUserSeeder extends Seeder
{
    public function run()
    {
        // Create Hospital
        $hospital = Hospital::firstOrCreate(
            ['domain' => 'hopital-centralma-plateforme.com'],
            [
                'name' => 'Hopital CentralMA',
                'database_name' => 'medkey_centralma',
                'status' => 'active',
                'slug' => 'hopital-centralma'
            ]
        );
        $this->command->info("Hospital ID: " . $hospital->id);

        // Create User
        $user = User::firstOrCreate(
            ['email' => 'admin@hopital-centralma-plateforme.com'],
            [
                'name' => 'Admin CentralMA',
                'password' => Hash::make('password'),
                'role_id' => 1
            ]
        );
        $this->command->info("User ID: " . $user->id);
    }
}
