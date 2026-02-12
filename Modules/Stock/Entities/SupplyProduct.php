<?php

namespace Modules\Stock\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Administration\Entities\Hospital;
class SupplyProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
protected $fillable = [
        'hospital_id',
        'units_per_box',
        'expire_date',
        'lot_number',
        'quantity',
        'purchase_price',
        'profit_margin',
        'supply_id',
        'product_id',
        'supplier_id',
        'is_synced',
        'uuid'
    ];

    protected $casts = ['is_synced' => 'boolean'];

    protected $dates = ['expire_date','deleted_at'];
}
