<?php

namespace Database\Factories\Core;

use App\Core\Models\Hospital;
use App\Core\Models\HospitalModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory pour le modèle HospitalModule (CORE)
 */
class HospitalModuleFactory extends Factory
{
    protected $model = HospitalModule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hospital_id' => Hospital::factory(),
            'module_name' => $this->faker->randomElement(['Patient', 'Stock', 'Cash', 'Payment', 'Acl']),
            'is_enabled' => true,
            'config' => [],
            'enabled_at' => now(),
            'disabled_at' => null,
        ];
    }

    /**
     * Indicate that the module is disabled.
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => false,
            'disabled_at' => now(),
            'enabled_at' => null,
        ]);
    }
}
