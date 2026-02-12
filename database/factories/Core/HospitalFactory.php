<?php

namespace Database\Factories\Core;

use App\Core\Models\Hospital;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory pour le modèle Hospital (CORE)
 */
class HospitalFactory extends Factory
{
    protected $model = Hospital::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company() . ' Hospital';
        $slug = Str::slug($name);

        return [
            'name' => $name,
            'domain' => $slug . '.medkey.com',
            'slug' => $slug,
            'database_name' => 'medkey_' . $slug,
            'database_host' => config('database.connections.mysql.host', '127.0.0.1'),
            'database_port' => config('database.connections.mysql.port', '3306'),
            'database_username' => config('database.connections.mysql.username'),
            'database_password' => config('database.connections.mysql.password'),
            'status' => 'active',
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'uuid' => (string) Str::uuid(),
        ];
    }

    /**
     * Indicate that the hospital is in provisioning status.
     */
    public function provisioning(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'provisioning',
        ]);
    }

    /**
     * Indicate that the hospital is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
