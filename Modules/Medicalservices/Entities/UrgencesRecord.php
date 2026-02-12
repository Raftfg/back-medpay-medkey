<?php

namespace Modules\Medicalservices\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Administration\Entities\Hospital;
class UrgencesRecord extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
protected $fillable = [
        'hospital_id',
        'uuid',
        'services_id',
        'movments_id',
        'category',
        'level',
        'description',
        'emergency_actions',
        'parent',
        'summary',
        'operator'
    ];

    protected static function newFactory()
    {
        return \Modules\Medicalservices\Database\factories\UrgencesRecordFactory::new();
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
