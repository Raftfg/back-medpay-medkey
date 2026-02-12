<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriseEnChargePatient extends Model
{

    use HasFactory;
    protected $table = 'priseenchargepatient';
    protected $fillable = [
        'is_synced',
        'patient_id',
        'assurance_id',
        'user_id',
        'pourcentage',
        'statut',
        'datedebut',
        'datefin',
        'compagnie_d_assurance_id',
        'observation',
        'numero_assurance',
        'deleted_at',

        // 'user_id',
        // 'centresanitaires_id',
    ];

    // Relations avec d'autres modèles

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function assurance()
    {
        return $this->belongsTo(Assurance::class, 'assurance_id');
    }

    public static function boot()
    {
        parent::boot();

        // Lorsqu'un terminal est créé, assignez-lui un ID dans l'intervalle requis
        static::creating(function ($PriseEnChargePatient) {
            $nextId = PriseEnChargePatient::max('id') + 1;
            $PriseEnChargePatient->id = max(5000001, min($nextId, 10000000));
        });
    }
}