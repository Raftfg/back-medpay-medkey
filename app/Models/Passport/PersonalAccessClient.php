<?php

namespace App\Models\Passport;

use App\Models\Passport\Concerns\ResolvesTenantConnection;
use Laravel\Passport\PersonalAccessClient as PassportPersonalAccessClient;

class PersonalAccessClient extends PassportPersonalAccessClient
{
    use ResolvesTenantConnection;

    /** @var string */
    protected $connection = 'tenant';
}
