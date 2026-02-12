<?php

namespace Modules\Annuaire\Entities;


use Modules\Annuaire\Entities\Contrat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Administration\Entities\Department;
use Modules\Administration\Entities\Service;

class Employer extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $connection = 'tenant';

    public function service()
    {
        return $this->belongsTo(Service::class, 'services_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'departments_id');
    }
}
