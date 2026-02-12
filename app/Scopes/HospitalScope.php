<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * HospitalScope (Squelette Neutralisé)
 * 
 * @deprecated Conservé uniquement pour la stabilité structurelle.
 */
class HospitalScope implements Scope
{
    /**
     * Appliquer le scope à la requête Eloquent.
     * NE RIEN FAIRE : L'isolation est assurée par la base de données.
     */
    public function apply(Builder $builder, Model $model)
    {
        // Aucun filtre hospital_id ici.
    }
}
