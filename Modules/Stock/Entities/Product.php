<?php

namespace Modules\Stock\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Acl\Entities\User;
use Modules\Administration\Entities\Hospital;
class Product extends Model
{
    use SoftDeletes, HasFactory;

    protected $connection = 'tenant';
protected $fillable = [
        'hospital_id',
        'uuid',
        'code',
        'name',
        'dosage',
        'brand',
        'conditioning_unit_id',
        'administration_route_id',
        'sale_unit_id',
        'category_id',
        'user_id',
        'is_synced',
        'type_id',
    ];

    protected $casts = ['is_synced' => 'boolean'];

    protected $dates = ['deleted_at']; 

    //Must be uncommented once the module User is created
    //
}
