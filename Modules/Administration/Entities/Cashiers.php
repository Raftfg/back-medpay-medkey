<?php

namespace Modules\Administration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cashiers extends Model
{
    use HasFactory;

    protected $fillable = ['name'];
    protected $connection = 'tenant';

    protected static function newFactory()
    {
        return \Modules\Administration\Database\factories\CashiersFactory::new();
    }
}
