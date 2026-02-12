<?php

namespace Modules\Administration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class commune extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'nom',
    //     'departement_id',
    // ];
    protected $guarded = [];
    protected $connection = 'tenant';
    // Relation avec le modèle "Pays" (en supposant que vous avez un modèle "Pays" associé à la table "pays")
    // Relation avec le modèle "Departement" (en supposant que vous avez un modèle "Departement" associé à la table "departements")
    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departements_id');
    }
    protected static function newFactory()
    {
        return \Modules\Administration\Database\factories\CommuneFactory::new();
    }
}
