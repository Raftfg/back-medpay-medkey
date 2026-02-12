<?php

namespace Modules\Movment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Allergie extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'type', // médicament, aliment, environnemental
        'severity', // léger, modéré, sévère, anaphylaxie
        'discovery_date',
        'reactions',
        'movments_id',
        'patients_id',
        'description'
    ];
    protected $connection = 'tenant';
    protected $appends =  ['human_arrival_date'];

    public function getHumanArrivalDateAttribute(){
       return $this->created_at->format("d/m/Y H:i");
   }
   protected static function newFactory()
   {
    return \Modules\Movment\Database\factories\AllergieFactory::new();
}
}
