<?php

namespace Modules\Administration\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Administration\Entities\HospitalSetting;
use Modules\Administration\Entities\Hospital;

/**
 * Factory pour HospitalSetting
 * 
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Modules\Administration\Entities\HospitalSetting>
 */
class HospitalSettingFactory extends Factory
{
    /**
     * Le nom du modèle correspondant à cette factory.
     *
     * @var string
     */
    protected $model = HospitalSetting::class;

    /**
     * Définit l'état par défaut du modèle.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hospital_id' => Hospital::factory(),
            'key' => $this->faker->unique()->word(),
            'value' => $this->faker->word(),
            'type' => 'string',
            'group' => 'general',
            'description' => $this->faker->sentence(),
            'is_public' => false,
        ];
    }

    /**
     * Indique que le paramètre est public.
     *
     * @return static
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Définit le groupe du paramètre.
     *
     * @param  string  $group
     * @return static
     */
    public function group(string $group): static
    {
        return $this->state(fn (array $attributes) => [
            'group' => $group,
        ]);
    }

    /**
     * Définit le type du paramètre.
     *
     * @param  string  $type
     * @return static
     */
    public function type(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }
}
