<?php

namespace Modules\Stock\Entities;

use Modules\Acl\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Administration\Entities\Hospital;
class Stock extends Model
{
    use HasFactory, SoftDeletes; 

    protected $connection = 'tenant';
protected $fillable = [
        'name',
        'store_id',
        'for_pharmacy_sale',
        'user_id',
        'hospital_id',
        'is_synced',
        'uuid',
    ];

    protected $casts = ['is_synced' => 'boolean'];

    protected $dates = ['deleted_at']; 

    //Must be uncommented once the module User is created
    //
}
