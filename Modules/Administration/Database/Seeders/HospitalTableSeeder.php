<?php

namespace Modules\Administration\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Administration\Entities\Hospital;
use Modules\Acl\Entities\User;
use Illuminate\Support\Str;

/**
 * Seeder pour la table hospitals
 * 
 * Crée plusieurs hôpitaux de test pour la plateforme multi-tenant
 * 
 * @package Modules\Administration\Database\Seeders
 */
class HospitalTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // Hôpitaux de test prédéfinis
        $hospitals = [
            [
                'name' => 'Hôpital Central de Casablanca',
                'domain' => 'hopital-central.ma-plateforme.com',
                'slug' => 'hopital-central',
                'status' => 'active',
                'address' => 'Boulevard Zerktouni, Casablanca',
                'phone' => '+212 522 123456',
                'email' => 'contact@hopital-central.ma',
                'description' => 'Hôpital de référence avec services complets',
            ],
            [
                'name' => 'Clinique Ibn Sina',
                'domain' => 'clinique-ibn-sina.ma-plateforme.com',
                'slug' => 'clinique-ibn-sina',
                'status' => 'active',
                'address' => 'Avenue Hassan II, Rabat',
                'phone' => '+212 537 234567',
                'email' => 'info@ibn-sina.ma',
                'description' => 'Clinique privée spécialisée en cardiologie',
            ],
            [
                'name' => 'Centre Hospitalier Universitaire Mohammed VI',
                'domain' => 'chu-mohammed6.ma-plateforme.com',
                'slug' => 'chu-mohammed6',
                'status' => 'active',
                'address' => 'Hay Riad, Rabat',
                'phone' => '+212 537 345678',
                'email' => 'contact@chu-mohammed6.ma',
                'description' => 'Centre hospitalier universitaire de référence',
            ],
            [
                'name' => 'Hôpital Moulay Youssef',
                'domain' => 'hopital-moulay-youssef.ma-plateforme.com',
                'slug' => 'hopital-moulay-youssef',
                'status' => 'active',
                'address' => 'Avenue Allal Ben Abdellah, Rabat',
                'phone' => '+212 537 456789',
                'email' => 'contact@moulay-youssef.ma',
                'description' => 'Hôpital public régional',
            ],
            [
                'name' => 'Clinique Agdal',
                'domain' => 'clinique-agdal.ma-plateforme.com',
                'slug' => 'clinique-agdal',
                'status' => 'inactive', // Pour tester les hôpitaux inactifs
                'address' => 'Quartier Agdal, Rabat',
                'phone' => '+212 537 567890',
                'email' => 'info@clinique-agdal.ma',
                'description' => 'Clinique privée en cours de configuration',
            ],
        ];

        // Récupérer le premier utilisateur admin pour created_by
        $adminUser = User::first();

        foreach ($hospitals as $hospitalData) {
            Hospital::updateOrCreate(
                ['domain' => $hospitalData['domain']],
                array_merge($hospitalData, [
                    'uuid' => (string) Str::uuid(),
                    'created_by' => $adminUser ? $adminUser->id : null,
                    'is_synced' => false,
                ])
            );
        }

        // Optionnel : Créer des hôpitaux supplémentaires avec la factory
        // Hospital::factory()->count(5)->active()->create([
        //     'created_by' => $adminUser ? $adminUser->id : null,
        // ]);

        $this->command->info('✅ ' . count($hospitals) . ' hôpitaux créés avec succès !');
        $this->command->info('📋 Hôpitaux disponibles :');
        foreach ($hospitals as $hospital) {
            $this->command->line("   - {$hospital['name']} ({$hospital['domain']}) - Status: {$hospital['status']}");
        }
        
        Model::reguard();
    }
}
