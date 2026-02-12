<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentreSanitaire extends Model
{
    use HasFactory;

    protected $table = 'centresanitaires';
    protected $fillable = [
        'nom',
        'departement_id',
        'commune_id',
        'arrondissement_id',
        'quartier_id',
        'adresse',
        'telephone',
        'email',
        'idtypecentresanitaire',
        'directeur',
        'reference',
        'user_id',
    ];

    public function typeCentreSanitaire()
    {
        return $this->belongsTo(TypeCentre::class, 'idtypecentresanitaire');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }
}
