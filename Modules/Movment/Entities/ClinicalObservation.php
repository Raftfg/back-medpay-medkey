<?php

namespace Modules\Movment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Patient\Entities\Patiente;

class ClinicalObservation extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';

    protected $fillable = [
        'uuid',
        'patients_id',
        'movments_id',
        'doctor_id',
        'subjective',
        'objective',
        'assessment',
        'plan',
        'blood_pressure',
        'heart_rate',
        'temperature',
        'respiratory_rate',
        'oxygen_saturation',
        'weight',
        'height',
        'observation_date',
        'type'
    ];

    protected $casts = [
        'observation_date' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Boot function pour générer automatiquement l'UUID
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->observation_date)) {
                $model->observation_date = now();
            }
        });
    }

    /**
     * Relation avec le patient
     */
    public function patient()
    {
        return $this->belongsTo(Patiente::class, 'patients_id');
    }

    /**
     * Relation avec le mouvement
     */
    public function movment()
    {
        return $this->belongsTo(Movment::class, 'movments_id');
    }

    /**
     * Relation avec le médecin (User)
     */
    public function doctor()
    {
        return $this->belongsTo(\App\Models\User::class, 'doctor_id');
    }

    /**
     * Scope pour filtrer par patient
     */
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patients_id', $patientId);
    }

    /**
     * Scope pour filtrer par type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
