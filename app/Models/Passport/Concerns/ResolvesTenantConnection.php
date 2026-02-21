<?php

namespace App\Models\Passport\Concerns;

use Illuminate\Support\Facades\Config;

/**
 * Quand la connexion 'tenant' n'a pas de base sélectionnée (requête hors contexte tenant),
 * évite l'erreur "No database selected" en utilisant la connexion par défaut.
 */
trait ResolvesTenantConnection
{
    /**
     * Get the current connection name for this model.
     */
    public function getConnectionName(): ?string
    {
        $tenantDb = Config::get('database.connections.tenant.database');
        if ($tenantDb === null || $tenantDb === '') {
            return Config::get('database.default');
        }
        return 'tenant';
    }
}
