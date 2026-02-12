<?php

namespace Modules\Payment\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OperationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\Payment\Entities\Operation::class;

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

