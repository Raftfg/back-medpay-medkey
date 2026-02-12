<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Payement;
use App\Models\MedicalAct;
use App\Models\Patient;
use App\Models\User;

class Facture extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'is_synced',
        'reference',
        'payement_id',
        'acte_medical_id',
        'patient_id',
        'user_id',
        'centre_id',
        'code',
        'autre',
        'prix',
        'quantite',
        'montant',
    ];


    public function payement()
    {
        return $this->belongsTo(Payement::class, '');
    }
    
    public function acteMedical()
    {
        return $this->belongsTo(MedicalAct::class, '');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, '');
    }

    public function user()
    {
        return $this->belongsTo(User::class, '');
    }

    public static function boot()
    {
        parent::boot();
        // Lorsqu'un patient est créé, assignez-lui un ID dans l'intervalle requis
        static::creating(function ($facture) {
            $nextId = Facture::max('id') + 1;
            $facture->id = max(5000001, min($nextId, 10000000));
        });
    }



}
