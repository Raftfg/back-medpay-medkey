<?php

namespace Modules\Annuaire\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Administration\Entities\Service;
use Modules\Acl\Entities\User;

class EmployerPerformance extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'criteria_scores' => 'array',
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class, 'employers_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'services_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_user_id');
    }
}

