<?php

namespace Modules\Rendezvous\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorAvailability extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'doctor_id',
        'service_id',
        'day_of_week', // 0 (dimanche) - 6 (samedi)
        'start_time',  // HH:MM
        'end_time',    // HH:MM
        'slot_duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'day_of_week' => 'integer',
        'slot_duration_minutes' => 'integer',
    ];

    public function doctor()
    {
        return $this->belongsTo(\Modules\Acl\Entities\User::class, 'doctor_id');
    }

    public function service()
    {
        return $this->belongsTo(\Modules\Administration\Entities\Service::class, 'service_id');
    }
}

