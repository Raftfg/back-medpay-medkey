<?php

namespace App\Models\Passport;

use App\Models\Passport\Concerns\ResolvesTenantConnection;
use Illuminate\Support\Str;
use Laravel\Passport\Client as PassportClient;

class Client extends PassportClient
{
    use ResolvesTenantConnection;

    /** @var string */
    protected $connection = 'tenant';

    /**
     * Garantit la génération d'un UUID pour oauth_clients (table en id UUID, pas auto-increment).
     */
    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($model): void {
            $keyName = $model->getKeyName();
            if (empty($model->{$keyName})) {
                $model->{$keyName} = (string) Str::orderedUuid();
            }
        });
    }
}
