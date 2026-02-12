<?php

namespace Modules\Movment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Antecedent extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'type', // médical, chirurgical, familial
        'cim10_code',
        'movments_id',
        'patients_id',
        'description',
        'start_date',
        'end_date',
        'is_cured'
    ];
    protected $connection = 'tenant';
protected $appends =  ['human_arrival_date'];

 public function getHumanArrivalDateAttribute(){
       return $this->created_at->format("d/m/Y H:i");
   }
    protected static function newFactory()
    {
        return \Modules\Movment\Database\factories\AntecedentFactory::new();
    }
}
