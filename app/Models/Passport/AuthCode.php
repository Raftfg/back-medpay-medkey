<?php

namespace App\Models\Passport;

use Laravel\Passport\AuthCode as PassportAuthCode;

class AuthCode extends PassportAuthCode
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'core';
}
