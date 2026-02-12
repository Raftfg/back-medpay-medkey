<?php

namespace App\Policies;

use Modules\Acl\Entities\User;
use Modules\Cash\Entities\CashRegister;

/**
 * Policy CashRegisterPolicy
 * 
 * Gère les autorisations d'accès aux caisses avec isolation multi-tenant.
 * 
 * @package App\Policies
 */
class CashRegisterPolicy extends BaseTenantPolicy
{
    /**
     * Détermine si l'utilisateur peut voir n'importe quelle caisse.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->belongsToCurrentHospital($user);
    }

    /**
     * Détermine si l'utilisateur peut voir la caisse.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Modules\Cash\Entities\CashRegister  $cashRegister
     * @return bool
     */
    public function view(User $user, CashRegister $cashRegister): bool
    {
        return $this->belongsToSameHospital($user, $cashRegister);
    }

    /**
     * Détermine si l'utilisateur peut créer des caisses.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->belongsToCurrentHospital($user)
            && $user->can('create cash registers');
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour la caisse.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Modules\Cash\Entities\CashRegister  $cashRegister
     * @return bool
     */
    public function update(User $user, CashRegister $cashRegister): bool
    {
        return $this->belongsToSameHospital($user, $cashRegister)
            && $user->can('update cash registers');
    }

    /**
     * Détermine si l'utilisateur peut supprimer la caisse.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Modules\Cash\Entities\CashRegister  $cashRegister
     * @return bool
     */
    public function delete(User $user, CashRegister $cashRegister): bool
    {
        return $this->belongsToSameHospital($user, $cashRegister)
            && $user->can('delete cash registers');
    }
}
