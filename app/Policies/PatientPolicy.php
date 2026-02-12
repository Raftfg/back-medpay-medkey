<?php

namespace App\Policies;

use Modules\Acl\Entities\User;
use Modules\Patient\Entities\Patiente;

/**
 * Policy PatientPolicy
 * 
 * Gère les autorisations d'accès aux patients avec isolation multi-tenant.
 * 
 * @package App\Policies
 */
class PatientPolicy
{
    /**
     * Détermine si l'utilisateur peut voir n'importe quel patient.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // L'utilisateur doit appartenir à l'hôpital courant
        return $user->hospital_id === currentHospitalId();
    }

    /**
     * Détermine si l'utilisateur peut voir le patient.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Modules\Patient\Entities\Patiente  $patient
     * @return bool
     */
    public function view(User $user, Patiente $patient): bool
    {
        // L'utilisateur doit appartenir à l'hôpital courant
        // L'isolation du patient est garantie par la base de données tenant
        return $user->hospital_id === currentHospitalId();
    }

    /**
     * Détermine si l'utilisateur peut créer des patients.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // L'utilisateur doit appartenir à l'hôpital courant
        // ET avoir la permission de créer des patients
        return $user->hospital_id === currentHospitalId()
            && $user->can('create patients');
    }

    /**
     * Détermine si l'utilisateur peut mettre à jour le patient.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Modules\Patient\Entities\Patiente  $patient
     * @return bool
     */
    public function update(User $user, Patiente $patient): bool
    {
        // L'utilisateur doit appartenir à l'hôpital courant et avoir la permission
        return $user->hospital_id === currentHospitalId()
            && $user->can('update patients');
    }

    /**
     * Détermine si l'utilisateur peut supprimer le patient.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Modules\Patient\Entities\Patiente  $patient
     * @return bool
     */
    public function delete(User $user, Patiente $patient): bool
    {
        // L'utilisateur doit appartenir à l'hôpital courant et avoir la permission
        return $user->hospital_id === currentHospitalId()
            && $user->can('delete patients');
    }

    /**
     * Détermine si l'utilisateur peut restaurer le patient.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Modules\Patient\Entities\Patiente  $patient
     * @return bool
     */
    public function restore(User $user, Patiente $patient): bool
    {
        return $user->hospital_id === currentHospitalId()
            && $user->can('restore patients');
    }

    /**
     * Détermine si l'utilisateur peut supprimer définitivement le patient.
     *
     * @param  \Modules\Acl\Entities\User  $user
     * @param  \Modules\Patient\Entities\Patiente  $patient
     * @return bool
     */
    public function forceDelete(User $user, Patiente $patient): bool
    {
        return $user->hospital_id === currentHospitalId()
            && $user->can('force delete patients');
    }
}
