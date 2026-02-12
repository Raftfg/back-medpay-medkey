<?php

namespace Modules\Stock\Entities;

use Modules\Acl\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Administration\Entities\Hospital;
class StockProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'tenant';
protected $fillable = [
        'hospital_id',
        'lot_number',
        'units_per_box',
        'expire_date',
        'quantity',
        'purchase_price',
        'selling_price',
        'product_id',
        'stock_id',
        'user_id',
        'is_synced',
        'uuid'
    ];

    protected $casts = ['is_synced' => 'boolean'];

    protected $dates = ['expire_date','deleted_at'];
}
