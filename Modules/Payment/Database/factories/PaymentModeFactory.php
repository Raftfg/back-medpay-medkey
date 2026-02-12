<?php

namespace Modules\Payment\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentModeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Payment\Entities\PaymentMode::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
        ];
    }
}

