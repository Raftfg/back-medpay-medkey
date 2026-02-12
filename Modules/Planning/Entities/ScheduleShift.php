<?php

namespace Modules\Planning\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Annuaire\Entities\Employer;
use Modules\Administration\Entities\Service;

class ScheduleShift extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
    
    protected $fillable = [
        'uuid',
        'work_schedule_id',
        'employer_id',
        'shift_date',
        'start_time',
        'end_time',
        'shift_type',
        'rotation_type',
        'service_id',
        'position',
        'status',
        'is_swap',
        'swapped_with_id',
        'duration_hours',
        'respects_legal_duration',
        'respects_rest_period',
        'notes',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_swap' => 'boolean',
        'respects_legal_duration' => 'boolean',
        'respects_rest_period' => 'boolean',
    ];

    /**
     * Relation avec le planning
     */
    public function workSchedule()
    {
        return $this->belongsTo(WorkSchedule::class, 'work_schedule_id');
    }

    /**
     * Relation avec l'employé
     */
    public function employer()
    {
        return $this->belongsTo(Employer::class, 'employer_id');
    }

    /**
     * Relation avec le service
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * Relation avec l'employé avec qui le shift a été échangé
     */
    public function swappedWith()
    {
        return $this->belongsTo(Employer::class, 'swapped_with_id');
    }

    /**
     * Scope pour les shifts d'un employé
     */
    public function scopeForEmployer($query, $employerId)
    {
        return $query->where('employer_id', $employerId);
    }

    /**
     * Scope pour une date donnée
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('shift_date', $date);
    }

    /**
     * Scope pour une période donnée
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('shift_date', [$startDate, $endDate]);
    }

    /**
     * Scope pour un service donné
     */
    public function scopeForService($query, $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }

    /**
     * Calculer la durée en heures
     */
    public function calculateDuration()
    {
        if ($this->start_time && $this->end_time) {
            $start = \Carbon\Carbon::parse($this->shift_date . ' ' . $this->start_time);
            $end = \Carbon\Carbon::parse($this->shift_date . ' ' . $this->end_time);
            
            // Si l'heure de fin est avant l'heure de début, c'est un shift de nuit
            if ($end->lt($start)) {
                $end->addDay();
            }
            
            return $start->diffInHours($end);
        }
        
        return null;
    }
}
