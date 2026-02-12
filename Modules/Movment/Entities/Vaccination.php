<?php

namespace Modules\Movment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Patient\Entities\Patiente;

class Vaccination extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';

    protected $fillable = [
        'uuid',
        'patients_id',
        'movments_id',
        'vaccine_name',
        'vaccine_code',
        'vaccination_date',
        'batch_number',
        'administration_route',
        'site',
        'notes',
        'doctor_id',
        'next_dose_date',
    ];

    protected $casts = [
        'vaccination_date' => 'date',
        'next_dose_date' => 'date',
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
     * Relation avec le médecin
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
}
