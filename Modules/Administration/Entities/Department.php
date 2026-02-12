<?php

namespace Modules\Administration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;
    protected $table = 'departments';
    protected $connection = 'tenant';
    protected $fillable = ['name', 'description']; // Colonnes remplissables

    protected static function newFactory()
    {
        return \Modules\Administration\Database\factories\DepartmentFactory::new();
    }
}
