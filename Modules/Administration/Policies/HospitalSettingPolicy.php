<?php

namespace Modules\Administration\Policies;

use Modules\Acl\Entities\User;
use Modules\Administration\Entities\HospitalSetting;
use App\Policies\BaseTenantPolicy;

/**
 * Policy pour HospitalSetting
 * 
 * Gère les autorisations d'accès aux paramètres d'hôpital.
 * Assure que seuls les utilisateurs de l'hôpital courant peuvent accéder/modifier les paramètres.
 * 
 * @package Modules\Administration\Policies
 */
class HospitalSettingPolicy extends BaseTenantPolicy
{
    /**
     * Détermine si l'utilisateur peut voir tous les paramètres
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Seuls les utilisateurs de l'hôpital courant peuvent voir les paramètres
        return $this->belongsToCurrentHospital($user);
    }

    /**
     * Détermine si l'utilisateur peut voir un paramètre spécifique
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Modules\Administration\Entities\HospitalSetting  $setting
     * @return bool
     */
    public function view(User $user, HospitalSetting $setting): bool
    {
        // Vérifier que l'utilisateur et le paramètre appartiennent au même hôpital
        return $this->belongsToSameHospital($user, $setting);
    }

    /**
     * Détermine si l'utilisateur peut créer un paramètre
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Seuls les utilisateurs de l'hôpital courant peuvent créer des paramètres
        // Optionnel : restreindre aux administrateurs
        return $this->belongsToCurrentHospital($user);
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour un paramètre
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Modules\Administration\Entities\HospitalSetting  $setting
     * @return bool
     */
    public function update(User $user, HospitalSetting $setting): bool
    {
        // Vérifier que l'utilisateur et le paramètre appartiennent au même hôpital
        return $this->belongsToSameHospital($user, $setting);
    }

    /**
     * Détermine si l'utilisateur peut supprimer un paramètre
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Modules\Administration\Entities\HospitalSetting  $setting
     * @return bool
     */
    public function delete(User $user, HospitalSetting $setting): bool
    {
        // Vérifier que l'utilisateur et le paramètre appartiennent au même hôpital
        // Optionnel : restreindre aux administrateurs
        return $this->belongsToSameHospital($user, $setting);
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour plusieurs paramètres
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @return bool
     */
    public function updateMany(User $user): bool
    {
        // Seuls les utilisateurs de l'hôpital courant peuvent mettre à jour des paramètres
        return $this->belongsToCurrentHospital($user);
    }
}
