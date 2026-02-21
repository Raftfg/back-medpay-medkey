<?php

namespace App\Models\Passport;

use App\Models\Passport\Concerns\ResolvesTenantConnection;
use Laravel\Passport\Token as PassportToken;

class Token extends PassportToken
{
    use ResolvesTenantConnection;

    /** @var string */
    protected $connection = 'tenant';
}
