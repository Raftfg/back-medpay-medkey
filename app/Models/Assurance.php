<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assurance extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        // 'montant',
        'pourcentage',
        'compagnie_d_assurance_id',
        'numero_assurance',
        // 'user_id',
        // 'centresanitaires_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    // protected $table = 'compagnies_d_assurance, assurances';

    // Relation avec la compagnie d'assurance
    public function compagnieAssurance()
    {
        return $this->belongsTo(CompagnieAssurance::class, 'compagnie_d_assurance_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function centresanitaire()
    {
        return $this->belongsTo(CentreSanitaire::class, 'idcentre');
    }
}
