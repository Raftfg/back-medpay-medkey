<?php

namespace Modules\Rendezvous\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'uuid',
        'patient_id',
        'doctor_id',
        'service_id',
        'scheduled_at',
        'duration_minutes',
        'type', // consultation, contrôle, téléconsultation, urgence, etc.
        'status', // pending, confirmed, cancelled, done, no_show
        'source', // on_site, online, phone
        'notes',
        'reminder_sent_at',
        'second_reminder_sent_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'second_reminder_sent_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(\Modules\Patient\Entities\Patiente::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(\Modules\Acl\Entities\User::class, 'doctor_id');
    }

    public function service()
    {
        return $this->belongsTo(\Modules\Administration\Entities\Service::class, 'service_id');
    }
}

