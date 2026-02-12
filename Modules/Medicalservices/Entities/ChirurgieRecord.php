<?php

namespace Modules\Medicalservices\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Patient\Entities\Patiente;
use Modules\Administration\Entities\MedicalAct;
use Modules\Administration\Entities\Hospital;
class ChirurgieRecord extends Model
{
  use HasFactory;

  protected $connection = 'tenant';
protected $fillable = [
    'hospital_id',
    'uuid',
    'services_id',
    'movments_id',
    'act_code',
    'reason',
    'description',
    'result',
    'summary',
    'operator',
    'status'
  ];

  protected $appends =  ['operatorname','actname'];

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

  public function getActnameAttribute(){
        if (!$this->act_code) {
            return 'N/A';
        }
        
        $Act = MedicalAct::where('code', $this->act_code)->first();
        if (!$Act) {
            return 'N/A';
        }
        
        return $Act->designation ?? 'N/A';
    }

  protected static function newFactory()
  {
    return \Modules\Medicalservices\Database\factories\ChirurgieRecordFactory::new();
  }
}
