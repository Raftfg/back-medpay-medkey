<?php

namespace App\Traits;

/**
 * Trait BelongsToHospital (Squelette Neutralisé)
 * 
 * @deprecated Ce trait est conservé uniquement pour éviter des erreurs de chargement.
 * L'isolation est désormais gérée par la séparation des bases de données.
 */
trait BelongsToHospital
{
    /**
     * Boot du modèle - Neutralisé
     */
    protected static function bootBelongsToHospital(): void
    {
        // Ne rien faire. Le Global Scope a été retiré.
    }

    /**
     * Scopes vides pour la compatibilité
     */
    public function scopeWithoutHospital($query) { return $query; }
    public function scopeForHospital($query, int $hospitalId) { return $query; }
    public function scopeAllHospitals($query) { return $query; }
}
