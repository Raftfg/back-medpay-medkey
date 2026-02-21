<?php

namespace App\Models\Passport;

use App\Models\Passport\Concerns\ResolvesTenantConnection;
use Laravel\Passport\AuthCode as PassportAuthCode;

class AuthCode extends PassportAuthCode
{
    use ResolvesTenantConnection;

    /** @var string */
    protected $connection = 'tenant';
}
