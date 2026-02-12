<?php

namespace Modules\Hospitalization\Entities;

use Modules\Acl\Entities\User;
use Modules\Patient\Entities\Patiente;
use Modules\Administration\Entities\Hospital;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class Bed extends Model
{
    protected $connection = 'tenant';
    protected $fillable = [
        'uuid',
        'room_id',
        'patient_id',
        'code',
        'name',
        'state', // busy, free, cleaning
        'user_id'
    ];

    public function patient()
    {
        return $this->belongsTo(Patiente::class, 'patient_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function bedPatients()
    {
        return $this->hasMany(BedPatient::class, 'bed_id');
    }

    public function currentStay()
    {
        return $this->hasOne(BedPatient::class, 'bed_id')->whereNull('end_occupation_date');
    }
}
