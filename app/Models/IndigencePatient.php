<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Patient;


class IndigencePatient extends Model
{
    use HasFactory;
    protected $fillable = [
        'is_synced',
        'indigence_id',
        'pourcentage',
        'patient_id',
        'user_id',
        'datedebut',
        'datefin',
        'deleted_at',
        // 'user_id',
        // 'centresanitaires_id',

    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function indigence()
    {
        return $this->belongsTo(Indigence::class);
    }
    protected $table = 'indigencepatient';

    public static function boot()
    {
        parent::boot();

        // Lorsqu'un terminal est créé, assignez-lui un ID dans l'intervalle requis
        static::creating(function ($indigencePatient) {
            $nextId = IndigencePatient::max('id') + 1;
            $indigencePatient->id = max(5000001, min($nextId, 10000000));
        });
    }
}
