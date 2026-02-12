<?php

namespace Modules\Stock\Entities;

use Modules\Acl\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Administration\Entities\Hospital;
class StockTransfer extends Model
{
    use SoftDeletes, HasFactory;

    protected $connection = 'tenant';
protected $fillable = [
        'hospital_id',
        'comment',
        'model_name',
        'model_id',
        'from_stock_id',
        'user_id',
        'is_synced',
        'uuid'
    ];

    protected $casts = ['is_synced' => 'boolean'];

    protected $dates = ['deleted_at']; 

    //Must be uncommented once the module User is created
    //
}
