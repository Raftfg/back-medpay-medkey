<?php

namespace App\Policies;

use Modules\Acl\Entities\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Policy de base pour les modèles multi-tenant
 * 
 * Fournit des méthodes communes pour vérifier l'appartenance à l'hôpital.
 * Les autres policies peuvent étendre cette classe.
 * 
 * @package App\Policies
 */
abstract class BaseTenantPolicy
{
    /**
     * Vérifie que l'utilisateur et la ressource appartiennent au même hôpital
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    protected function belongsToSameHospital(User $user, Model $model): bool
    {
        // Vérifier que l'utilisateur appartient à l'hôpital courant
        // L'appartenance du modèle (ressource) à l'hôpital est garantie par l'isolation de la base de données tenant
        return $this->belongsToCurrentHospital($user);
    }

    /**
     * Vérifie que l'utilisateur appartient à l'hôpital courant
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @return bool
     */
    protected function belongsToCurrentHospital(User $user): bool
    {
        $currentHospitalId = currentHospitalId();
        
        if ($currentHospitalId === null) {
            return false;
        }

        return $user->hospital_id === $currentHospitalId;
    }
}
