<?php

namespace Modules\Stock\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Administration\Entities\Hospital;
class SaleProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
protected $fillable = [
        'hospital_id',
        'price',
        'quantity',
        'sale_id',
        'stock_products_id',
        'user_id',
        'is_synced',
        'uuid'
    ];

    protected $casts = ['is_synced' => 'boolean'];

    protected $dates = ['deleted_at']; 

    //Must be uncommented once the module User and Movement are created
    //
}
