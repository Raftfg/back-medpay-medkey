<?php

namespace Modules\Planning\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Annuaire\Entities\Employer;
use Modules\Administration\Entities\Service;

class WorkSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
    
    protected $fillable = [
        'uuid',
        'employer_id',
        'service_id',
        'start_date',
        'end_date',
        'period_type',
        'status',
        'is_active',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
    ];

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
     * Relation avec les shifts
     */
    public function shifts()
    {
        return $this->hasMany(ScheduleShift::class, 'work_schedule_id');
    }

    /**
     * Relation avec le créateur
     */
    public function creator()
    {
        return $this->belongsTo(\Modules\Acl\Entities\User::class, 'created_by');
    }

    /**
     * Relation avec l'approbateur
     */
    public function approver()
    {
        return $this->belongsTo(\Modules\Acl\Entities\User::class, 'approved_by');
    }

    /**
     * Scope pour les plannings actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les plannings publiés
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope pour une période donnée
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->where(function($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function($q2) use ($startDate, $endDate) {
                  $q2->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        });
    }
}
