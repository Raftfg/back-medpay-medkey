<?php

namespace Modules\Administration\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Administration\Entities\Insurance;
// use Modules\Administration\Entities\ProductType;
use Modules\Patient\Entities\PatientInsurance;
use Modules\Administration\Entities\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Administration\Database\factories\PackFactory;

class Pack extends Model
{
    use HasFactory;

    protected $table = 'packs'; // Nom de la table
    protected $connection = 'tenant';

    // protected $fillable = ['designation', 'percentage', 'insurances_id'];
    protected $guarded = [];

    // Relation avec la table "insurances"
    public function insurance()
    {
        return $this->belongsTo(Insurance::class, 'insurances_id');
    }

   
    // Relation avec la table "product_types"
    // public function productType()
    // {
    //     return $this->belongsTo(ProductType::class, 'product_types_id');
    // }

    protected static function newFactory()
    {
        return \Modules\Administration\Database\factories\PackFactory::new();
    }
}
