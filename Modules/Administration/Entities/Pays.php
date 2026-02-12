<?php

namespace Modules\Administration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class pays extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    
    protected $fillable = [
        'nom',
    ];

    protected static function newFactory()
    {
        return \Modules\Administration\Database\factories\PaysFactory::new();
    }
}
