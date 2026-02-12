<?php

namespace App\Models\Passport;

use Laravel\Passport\PersonalAccessClient as PassportPersonalAccessClient;

class PersonalAccessClient extends PassportPersonalAccessClient
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'core';
}
