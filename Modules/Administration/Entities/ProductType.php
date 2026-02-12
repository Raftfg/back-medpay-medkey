<?php

namespace Modules\Administration\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductType extends Model
{
    use HasFactory;

    protected $table = 'product_types'; // Nom de la table
    protected $connection = 'tenant';

    // protected $fillable = ['designation', 'description']; // Colonnes remplissables
    protected $guarded = [];
    protected static function newFactory()
    {
        return \Modules\Administration\Database\factories\ProductTypeFactory::new();
    }
}
