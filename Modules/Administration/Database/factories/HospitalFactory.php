<?php

namespace Modules\Administration\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Administration\Entities\Hospital;

/**
 * Factory pour le modèle Hospital
 * 
 * Génère des données de test pour les hôpitaux (tenants)
 * 
 * @package Modules\Administration\Database\factories
 */
class HospitalFactory extends Factory
{
    /**
     * Le nom du modèle correspondant à cette factory
     *
     * @var string
     */
    protected $model = Hospital::class;

    /**
     * Définit l'état par défaut du modèle
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->company() . ' Hospital';
        $slug = \Illuminate\Support\Str::slug($name);
        
        return [
            'name' => $name,
            'domain' => $slug . '.' . $this->faker->domainName(),
            'slug' => $slug,
            'status' => $this->faker->randomElement(['active', 'active', 'active', 'inactive']), // 75% actifs
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'logo' => null, // À implémenter si nécessaire
            'description' => $this->faker->optional()->sentence(),
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'created_by' => null, // Sera défini dans le seeder
            'is_synced' => false,
        ];
    }

    /**
     * État : Hôpital actif
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function active()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'active',
            ];
        });
    }

    /**
     * État : Hôpital inactif
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'inactive',
            ];
        });
    }

    /**
     * État : Hôpital suspendu
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function suspended()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'suspended',
            ];
        });
    }
}
