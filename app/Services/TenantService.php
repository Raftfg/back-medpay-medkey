<?php

namespace App\Services;

use App\Core\Models\Hospital;
use Illuminate\Support\Facades\Cache;

/**
 * Service TenantService
 * 
 * Fournit des méthodes utilitaires pour accéder au tenant (hôpital) courant.
 * 
 * @package App\Services
 */
class TenantService
{
    /**
     * Récupère l'hôpital courant
     *
     * @return \App\Core\Models\Hospital|null
     */
    public static function current(): ?Hospital
    {
        // Essayer de récupérer depuis l'application
        if (app()->bound('hospital')) {
            return app('hospital');
        }

        // Essayer de récupérer depuis la session
        if (session()->has('hospital_id')) {
            $hospitalId = session('hospital_id');
            return Cache::remember("hospital_{$hospitalId}", 3600, function () use ($hospitalId) {
                return Hospital::find($hospitalId);
            });
        }

        // Essayer de récupérer depuis la requête (si disponible)
        if (request()->has('hospital_id')) {
            $hospitalId = request()->get('hospital_id');
            return Cache::remember("hospital_{$hospitalId}", 3600, function () use ($hospitalId) {
                return Hospital::find($hospitalId);
            });
        }

        return null;
    }

    /**
     * Récupère l'ID de l'hôpital courant
     *
     * @return int|null
     */
    public static function currentId(): ?int
    {
        $hospital = self::current();
        return $hospital ? $hospital->id : null;
    }

    /**
     * Vérifie si un tenant est défini
     *
     * @return bool
     */
    public static function hasTenant(): bool
    {
        return self::current() !== null;
    }

    /**
     * Définit le tenant courant (utile pour les tests ou l'administration)
     *
     * @param  \App\Core\Models\Hospital|int  $hospital
     * @return void
     */
    public static function setTenant($hospital): void
    {
        if (is_int($hospital)) {
            $hospital = Hospital::findOrFail($hospital);
        }

        app()->instance('hospital', $hospital);
        app()->instance('hospital_id', $hospital->id);

        if (session()->isStarted()) {
            session()->put('hospital_id', $hospital->id);
            session()->put('hospital', $hospital->toArray());
        }
    }

    /**
     * Réinitialise le tenant (utile pour les tests)
     *
     * @return void
     */
    public static function reset(): void
    {
        app()->forgetInstance('hospital');
        app()->forgetInstance('hospital_id');

        if (session()->isStarted()) {
            session()->forget('hospital_id');
            session()->forget('hospital');
        }
    }

    /**
     * Récupère tous les hôpitaux actifs
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function activeHospitals()
    {
        return Cache::remember('active_hospitals', 3600, function () {
            return Hospital::active()->get();
        });
    }
}
