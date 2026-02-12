<?php

namespace Modules\Administration\Entities;
use Modules\Acl\Entities\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Administration\Entities\Pack;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Administration\Database\factories\InsuranceFactory;

class Insurance extends Model
{
    use HasFactory;

    // protected $fillable = ['name', 'number', 'insuranceComp', 'is_convention', 'phone'];
    protected $guarded = [];
    protected $connection = 'tenant';


    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function packs()
    {
        return $this->hasMany(Pack::class, 'insurances_id');
    }

   
    // Ajoutez cette fonction pour obtenir l'assurance et les packs associés
    public static function getInsuranceWithPacks($insuranceId)
    {
        return self::with('packs')->find($insuranceId);
    }

    protected static function newFactory()
    {
        return \Modules\Administration\Database\factories\InsuranceFactory::new();
    }
}
