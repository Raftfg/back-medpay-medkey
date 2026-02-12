<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Momo extends Model
{
    use HasFactory;

    protected $fillable = [
        'mode_payements_id',
        'payement_id',
        'nom',
        'prenom',
        'telephone',
        'montant',
        'transaction_id',
        'statut',
      
    ];

}
