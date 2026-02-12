<?php

namespace Modules\Stock\Entities;

use Modules\Acl\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Administration\Entities\Hospital;
class Supply extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $connection = 'tenant';
protected $fillable = [
        'hospital_id',
        'numero',
        'total',
        'stock_id',
        'user_id',
        'is_synced',
        'uuid'
    ];

    protected $casts = ['is_synced' => 'boolean'];

    protected $dates = ['date','deleted_at'];
}
