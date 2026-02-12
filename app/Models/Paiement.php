<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Patient;
use App\Models\ModePaiement;


class Paiement extends Model
{
    use HasFactory;
    // protected $table = 'paiement';
    protected $fillable = ['patient_id', 'idmodepaiement'];

    // Relation avec le modèle Patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // Relation avec le modèle ModePaiement
    public function modePaiement()
    {
        return $this->belongsTo(ModePaiement::class, 'idmodepaiement');
    }
}
