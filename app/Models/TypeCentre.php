<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeCentre extends Model
{
    use HasFactory;
    protected $table = 'type_centre_sanitaire';
    protected $fillable = [
        'nom',
    ];

    // Relation avec le modèle CentreSanitaire
    // public function centresSanitaires()
    // {
    //     return $this->hasMany(centresanitaires::class, 'idtypecentresanitaire', 'idtypecentresanitaire');
    // }
}