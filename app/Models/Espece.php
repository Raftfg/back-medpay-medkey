<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Espece extends Model
{
    use HasFactory;

    protected $fillable = [
        'is_synced',
        'mode_payements_id',
        'payement_id',
        'montant'
    ];

    public static function boot()
    {
        parent::boot();
        // Lorsqu'un patient est créé, assignez-lui un ID dans l'intervalle requis
        static::creating(function ($espece) {
            $nextId = Espece::max('id') + 1;
            $espece->id = max(5000001, min($nextId, 10000000));
        });
    }

}
