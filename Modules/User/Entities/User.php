<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;

    protected $fillable = [];
    protected $connection = 'tenant';
    
    protected static function newFactory()
    {
        return \Modules\User\Database\factories\UserFactory::new();
    }
}
