<?php

namespace Modules\Stock\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Administration\Entities\Hospital;
class StockTransferProduct extends Model
{
    use SoftDeletes, HasFactory;

    protected $connection = 'tenant';
protected $fillable = [
        'hospital_id',
        'quantity_transfered',
        'stock_product_id',
        'stock_transfer_id',
        'is_synced',
        'uuid'
    ];

    protected $casts = ['is_synced' => 'boolean'];

    protected $dates = ['deleted_at'];
}
