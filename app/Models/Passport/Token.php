<?php

namespace App\Models\Passport;

use Laravel\Passport\Token as PassportToken;

class Token extends PassportToken
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'core';
}
