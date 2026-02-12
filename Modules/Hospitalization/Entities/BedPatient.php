<?php

namespace Modules\Hospitalization\Entities;

use Modules\Acl\Entities\User;
use Modules\Patient\Entities\Patiente;
use Modules\Administration\Entities\Hospital;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class BedPatient extends Model
{
    protected $connection = 'tenant';
    protected $fillable = [
        'uuid',
        'bed_id',
        'patient_id',
        'movment_id',
        'start_occupation_date',
        'end_occupation_date',
        'number_of_days',
        'state',
        'user_id'
    ];

    public function bed()
    {
        return $this->belongsTo(Bed::class, 'bed_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patiente::class, 'patient_id');
    }

    public function movment()
    {
        return $this->belongsTo(\Modules\Movment\Entities\Movment::class, 'movment_id');
    }
}
