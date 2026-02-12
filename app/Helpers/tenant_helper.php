<?php

/**
 * Helper functions pour le multi-tenant
 * 
 * Ces fonctions facilitent l'accès au tenant (hôpital) courant dans toute l'application.
 * 
 * @package App\Helpers
 */

use App\Services\TenantService;
use Modules\Administration\Entities\Hospital;

if (!function_exists('currentHospital')) {
    /**
     * Récupère l'hôpital courant
     *
     * @return \Modules\Administration\Entities\Hospital|null
     */
    function currentHospital(): ?Hospital
    {
        return TenantService::current();
    }
}

if (!function_exists('currentHospitalId')) {
    /**
     * Récupère l'ID de l'hôpital courant
     *
     * @return int|null
     */
    function currentHospitalId(): ?int
    {
        return TenantService::currentId();
    }
}

if (!function_exists('hasTenant')) {
    /**
     * Vérifie si un tenant est défini
     *
     * @return bool
     */
    function hasTenant(): bool
    {
        return TenantService::hasTenant();
    }
}

if (!function_exists('setTenant')) {
    /**
     * Définit le tenant courant (utile pour les tests ou l'administration)
     *
     * @param  \Modules\Administration\Entities\Hospital|int  $hospital
     * @return void
     */
    function setTenant($hospital): void
    {
        TenantService::setTenant($hospital);
    }
}

if (!function_exists('resetTenant')) {
    /**
     * Réinitialise le tenant (utile pour les tests)
     *
     * @return void
     */
    function resetTenant(): void
    {
        TenantService::reset();
    }
}

if (!function_exists('hospitalSetting')) {
    /**
     * Récupère un paramètre de l'hôpital courant
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function hospitalSetting(string $key, $default = null)
    {
        return app(\App\Services\HospitalSettingsService::class)->get($key, $default);
    }
}

if (!function_exists('hospitalSettings')) {
    /**
     * Récupère tous les paramètres de l'hôpital courant
     *
     * @return \Illuminate\Support\Collection
     */
    function hospitalSettings(): \Illuminate\Support\Collection
    {
        return app(\App\Services\HospitalSettingsService::class)->all();
    }
}

if (!function_exists('hospitalSettingsGroup')) {
    /**
     * Récupère les paramètres d'un groupe spécifique
     *
     * @param  string  $group
     * @return \Illuminate\Support\Collection
     */
    function hospitalSettingsGroup(string $group): \Illuminate\Support\Collection
    {
        return app(\App\Services\HospitalSettingsService::class)->getGroup($group);
    }
}

if (!function_exists('hospitalPublicSettings')) {
    /**
     * Récupère les paramètres publics de l'hôpital courant
     *
     * @return \Illuminate\Support\Collection
     */
    function hospitalPublicSettings(): \Illuminate\Support\Collection
    {
        return app(\App\Services\HospitalSettingsService::class)->getPublic();
    }
}
