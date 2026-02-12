<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FedapayTransaction  extends Model
{
    use HasFactory;

    protected $table = 'fedapay-transaction';

    protected $fillable = [
        'mode_payements_id',
        'payement_id',
        'libelle_mode',

    
    ];

}
