<?php

namespace Modules\Absence\Entities;

use Modules\Acl\Entities\User;
use Modules\Absence\Entities\Mission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MissionParticipant extends Model
{
    protected $guarded = [];
    protected $connection = 'tenant';

    use HasFactory;
    
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function mission()
    {
        return $this->belongsTo(Mission::class, 'missions_id');
    }
}
