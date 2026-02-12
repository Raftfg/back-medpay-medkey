<?php

namespace Modules\Absence\Entities;

use Modules\Acl\Entities\User;
use Modules\Absence\Entities\Absent;
use Illuminate\Database\Eloquent\Model;
use Modules\Absence\Entities\TypeVacation;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Vacation extends Model
{
    protected $guarded = [];
    protected $connection = 'tenant';
    use HasFactory;
    
    public function typeVacation(){
        return $this->belongsTo(TypeVacation::class, 'type_vacations_id');

    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function department()
    {
        // return $this->belongsTo(Department::class, 'departments_id');
    }

    public function absent()
    {
        return $this->hasOne(Absent::class, 'vacations_id');
    }

}
