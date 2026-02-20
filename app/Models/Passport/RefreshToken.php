<?php

namespace App\Models\Passport;

use App\Models\Passport\Concerns\ResolvesTenantConnection;
use Laravel\Passport\RefreshToken as PassportRefreshToken;

class RefreshToken extends PassportRefreshToken
{
    use ResolvesTenantConnection;

    /** @var string */
    protected $connection = 'tenant';
}
