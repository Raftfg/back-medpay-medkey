<?php

namespace App\Models\Passport;

use Laravel\Passport\RefreshToken as PassportRefreshToken;

class RefreshToken extends PassportRefreshToken
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'core';
}
