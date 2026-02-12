<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Patient;
use App\Models\ModePayement;

class Payement extends Model
{
    use HasFactory;

    protected $table = 'payements';
    protected $fillable = ['is_synced','patient_id', 'mode_payement_id'];

    // Relation avec le modèle Patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // Relation avec le modèle ModePaiement
    public function modePaiement()
    {
        return $this->belongsTo(ModePayement::class, 'mode_payement_id');
    }


    public static function boot()
    {
        parent::boot();
        // Lorsqu'un patient est créé, assignez-lui un ID dans l'intervalle requis
        static::creating(function ($payement) {
            $nextId = Payement::max('id') + 1;
            $payement->id = max(5000001, min($nextId, 10000000));
        });
    }
  
}
