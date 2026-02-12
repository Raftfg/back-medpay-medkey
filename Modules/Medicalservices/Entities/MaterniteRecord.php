<?php

namespace Modules\Medicalservices\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Patient\Entities\Patiente;
use Modules\Administration\Entities\Hospital;
class MaterniteRecord extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
protected $fillable = [
        'hospital_id',
        'uuid',
        'services_id',
        'movments_id',
        'reason',
        'complaint',
        'actions',
        'observation',
        'summary',
        'operator'
    ];

    protected $appends =  ['operatorname'];
    
    protected static function newFactory()
    {
        return \Modules\Medicalservices\Database\factories\MaterniteRecordFactory::new();
    }

      public function getOperatornameAttribute(){
        if (!$this->operator) {
            return 'N/A';
        }
        
        $Patient = Patiente::find($this->operator);
        if (!$Patient) {
            return 'N/A';
        }
        
        return ($Patient->lastname ?? '') . " " . ($Patient->firstname ?? '');
    }
}
