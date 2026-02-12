<?php

namespace Modules\Movment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Modules\Patient\Entities\Patiente;

class DmeDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
    protected $table = 'dme_documents';

    protected $fillable = [
        'uuid',
        'patients_id',
        'movments_id',
        'clinical_observation_id',
        'title',
        'type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'description',
        'uploaded_by',
        'document_date',
    ];

    protected $casts = [
        'document_date' => 'date',
        'file_size' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->document_date)) {
                $model->document_date = now();
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

    public function uploadedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patients_id', $patientId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
