<?php

namespace Database\Factories;
use App\Models\ActeMedical;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Generator as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ModePayement>
 */
use App\Models\ModePayement;

class ModePayementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = ModePayement::class;


    public function definition(): array
    {

        $mode = ['Espèce', 'Carte crédit', 'MOMO', 'Flooz', 'Carte Bancaire'];
        // shuffle($mode)

        return [
            //
                
            // 'id' => $this->faker->sequence(),
            'mode' =>  $this->faker->randomElement($mode),
      
            
        ];
    }
}
