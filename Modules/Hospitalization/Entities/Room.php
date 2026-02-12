<?php

namespace Modules\Hospitalization\Entities;

use Modules\Acl\Entities\User;
use Modules\Administration\Entities\Hospital;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $connection = 'tenant';
    
    protected $fillable = [
        'uuid',
        'hospital_id',
        'services_id',
        'code',
        'name',
        'bed_capacity',
        'price',
        'description',
        'user_id'
    ];
    
    /**
     * Relation avec les lits
     */
    public function beds()
    {
        return $this->hasMany(Bed::class, 'room_id');
    }
    
    /**
     * Relation avec le service
     */
    public function service()
    {
        return $this->belongsTo(\Modules\Administration\Entities\Service::class, 'services_id');
    }
}
