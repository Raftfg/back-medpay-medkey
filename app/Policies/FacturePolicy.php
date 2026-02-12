<?php

namespace App\Policies;

use Modules\Acl\Entities\User;
use Modules\Payment\Entities\Facture;

/**
 * Policy FacturePolicy
 * 
 * Gère les autorisations d'accès aux factures avec isolation multi-tenant.
 * 
 * @package App\Policies
 */
class FacturePolicy extends BaseTenantPolicy
{
    /**
     * Détermine si l'utilisateur peut voir n'importe quelle facture.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->belongsToCurrentHospital($user);
    }

    /**
     * Détermine si l'utilisateur peut voir la facture.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Modules\Payment\Entities\Facture  $facture
     * @return bool
     */
    public function view(User $user, Facture $facture): bool
    {
        return $this->belongsToSameHospital($user, $facture);
    }

    /**
     * Détermine si l'utilisateur peut créer des factures.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->belongsToCurrentHospital($user)
            && $user->can('create factures');
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour la facture.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Modules\Payment\Entities\Facture  $facture
     * @return bool
     */
    public function update(User $user, Facture $facture): bool
    {
        return $this->belongsToSameHospital($user, $facture)
            && $user->can('update factures');
    }

    /**
     * Détermine si l'utilisateur peut supprimer la facture.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Modules\Payment\Entities\Facture  $facture
     * @return bool
     */
    public function delete(User $user, Facture $facture): bool
    {
        return $this->belongsToSameHospital($user, $facture)
            && $user->can('delete factures');
    }

    /**
     * Détermine si l'utilisateur peut valider la facture.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Modules\Payment\Entities\Facture  $facture
     * @return bool
     */
    public function validate(User $user, Facture $facture): bool
    {
        return $this->belongsToSameHospital($user, $facture)
            && $user->can('validate factures');
    }
}
