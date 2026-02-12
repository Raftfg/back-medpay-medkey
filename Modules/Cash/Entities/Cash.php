<?php

namespace Modules\Cash\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cash extends Model
{
    use HasFactory;

    protected $fillable = [];
    protected $connection = 'tenant';
    
    // protected static function newFactory()
    // {
    //     return \Modules\Cash\Database\factories\CashFactory::new();
    // }
}
