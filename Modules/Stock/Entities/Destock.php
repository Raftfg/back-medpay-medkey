<?php

namespace Modules\Stock\Entities;

use Modules\Acl\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Administration\Entities\Hospital;
class Destock  extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
protected $fillable = [
        'hospital_id',
        'reference_facture',
        'quantity_retrieved',
        'quantity_ordered',
        'stock_product_id',
        'user_id',
        'is_synced',
        'type_id',
    ];

    protected $casts = ['is_synced' => 'boolean'];

    protected $dates = ['deleted_at']; 

    //Must be uncommented once the module User is created
}
