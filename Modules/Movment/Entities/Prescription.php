<?php

namespace Modules\Movment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Patient\Entities\Patiente;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';

    protected $fillable = [
        'uuid',
        'patients_id',
        'movments_id',
        'clinical_observation_id',
        'doctor_id',
        'prescription_date',
        'notes',
        'status',
        'valid_until',
    ];

    protected $casts = [
        'prescription_date' => 'date',
        'valid_until' => 'date',
    ];

    protected $dates = ['deleted_at'];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->prescription_date)) {
                $model->prescription_date = now();
            }
        });
    }

    public function patient()
    {
        return $this->belongsTo(Patiente::class, 'patients_id');
    }

    public function movment()
    {
        return $this->belongsTo(Movment::class, 'movments_id');
    }

    public function clinicalObservation()
    {
        return $this->belongsTo(ClinicalObservation::class, 'clinical_observation_id');
    }

    public function doctor()
    {
        return $this->belongsTo(\App\Models\User::class, 'doctor_id');
    }

    public function items()
    {
        return $this->hasMany(PrescriptionItem::class, 'prescription_id');
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patients_id', $patientId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
