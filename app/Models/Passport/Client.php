<?php

namespace App\Models\Passport;

use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'core';
}
