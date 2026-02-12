<?php

/**
 * Helper functions pour le multi-tenant (CORE)
 * 
 * Ces fonctions facilitent l'accès au tenant (hôpital) courant et à la connexion tenant.
 * 
 * @package App\Core\Helpers
 */

use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;

if (!function_exists('currentTenant')) {
    /**
     * Récupère l'hôpital courant (tenant)
     * 
     * Cette fonction utilise TenantConnectionService qui applique la logique suivante :
     * 1. Hôpital actuellement connecté via le service
     * 2. Hôpital de l'utilisateur authentifié (auth()->user()->hospital_id)
     * 3. Hôpital depuis le container de l'application
     *
     * @return \App\Core\Models\Hospital|null
     */
    function currentTenant(): ?Hospital
    {
        return app(TenantConnectionService::class)->getCurrentHospital();
    }
}

if (!function_exists('currentTenantId')) {
    /**
     * Récupère l'ID de l'hôpital courant (tenant)
     *
     * @return int|null
     */
    function currentTenantId(): ?int
    {
        $hospital = currentTenant();
        return $hospital ? $hospital->id : null;
    }
}

if (!function_exists('isTenantConnected')) {
    /**
     * Vérifie si une connexion tenant est active
     *
     * @return bool
     */
    function isTenantConnected(): bool
    {
        return app(TenantConnectionService::class)->isConnected();
    }
}

if (!function_exists('tenantConnection')) {
    /**
     * Récupère la connexion tenant courante
     *
     * @return \Illuminate\Database\Connection|null
     */
    function tenantConnection(): ?\Illuminate\Database\Connection
    {
        return app(TenantConnectionService::class)->getCurrentConnection();
    }
}

if (!function_exists('connectTenant')) {
    /**
     * Connecte à un tenant spécifique
     *
     * @param  \App\Core\Models\Hospital|int  $hospital
     * @return void
     * @throws \Exception
     */
    function connectTenant($hospital): void
    {
        if (is_int($hospital)) {
            $hospital = \App\Core\Models\Hospital::findOrFail($hospital);
        }
        
        app(TenantConnectionService::class)->connect($hospital);
    }
}

if (!function_exists('disconnectTenant')) {
    /**
     * Déconnecte du tenant courant
     *
     * @return void
     */
    function disconnectTenant(): void
    {
        app(TenantConnectionService::class)->disconnect();
    }
}
