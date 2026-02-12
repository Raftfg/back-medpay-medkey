<?php

namespace Modules\Administration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class MedicalAct extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
protected $fillable = [
        'hospital_id', 'code', 'designation', 'description', 'price', 'services_id', 'type_medical_acts_id'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'services_id');
    }

    public function typeMedicalAct()
    {
        return $this->belongsTo(TypeMedicalAct::class, 'type_medical_acts_id');
    }

    protected static function newFactory()
    {
        return \Modules\Administration\Database\factories\MedicalActFactory::new();
    }
}
