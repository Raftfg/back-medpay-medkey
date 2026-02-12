<?php

namespace Modules\Administration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description']; // Colonnes remplissables
    protected $connection = 'tenant';

    protected static function newFactory()
    {
        return \Modules\Administration\Database\factories\CashCategoryFactory::new();
    }
}
