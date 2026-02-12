<?php

namespace Modules\Annuaire\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Annuaire\Entities\Employer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contrat extends Model
{

    protected $guarded = [];
    protected $connection = 'tenant';
    use HasFactory;

    public function employee()
    {
        return $this->belongsTo(Employer::class, 'employee_id');
    }
}
