<?php

namespace Modules\Recouvrement\Entities;

use Illuminate\Support\Str;
use Modules\Movment\Entities\Movment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Recouvrement\Database\factories\RecouvreFactory;
use Modules\Administration\Entities\Hospital;
class Recouvre extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'recouvrements';
    protected $connection = 'tenant';

    protected $fillable = [
    'hospital_id',
    'uuid',
    'user_id',
    'is_synced',
    'reference_facture',
    'type',
    'movement_id',
    'mode_payements_id',
    'montant_facture',
    'pourcentage_assurance',
    'montant_saisi',
    'date_recouvrement',
    ];
    
    // protected static function newFactory(): RecouvreFactory
    // {
    //     //return RecouvreFactory::new();
    // }

    public function movment()
    {
        return $this->belongsTo(Movment::class, 'movments_id');
    }

    

    






    public static function boot()
    {
        parent::boot();
        static::creating(function ($recouvrement) {
            $recouvrement->uuid = (string) Str::uuid();
        });
    }


}
