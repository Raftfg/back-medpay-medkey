<?php

namespace Modules\Movment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Administration\Entities\Hospital;
class Movment extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'hospital_id',
        'patients_id',
        'ipp',
        'iep',
        'arrivaldate',
        'releasedate',
        'incoming_reason',
        'outgoing_reason',
        'admission_type', // programmée, urgence
        'responsible_doctor_id',
        'active_services_id',
        'active_services_code',
        'is_synced'
    ];
    protected $connection = 'tenant';

    protected $appends =  ['human_arrival_date','human_release_date','getout'];


    public function getHumanArrivalDateAttribute(){
     return convertToFrenchDate($this->created_at);
 }

 public function getHumanReleaseDateAttribute(){
     return convertToFrenchDate($this->releasedate);
 }


 public function getGetoutAttribute(){
    /* $paid = DB::table('patient_movement_details')
     ->where('movments_id',$this->id)
     ->where('paid',1)->first();
     if($paid){*/
        return $this->releasedate;
     /*}else{
         return 0;
     }*/
 }





    public function bedPatients()
    {
        return $this->hasMany(\Modules\Hospitalization\Entities\BedPatient::class, 'movment_id');
    }

    public function patient()
    {
        return $this->belongsTo(\Modules\Patient\Entities\Patiente::class, 'patients_id');
    }

    public function doctor()
    {
        return $this->belongsTo(\Modules\Acl\Entities\User::class, 'responsible_doctor_id');
    }

    public function service()
    {
        return $this->belongsTo(\Modules\Administration\Entities\Service::class, 'active_services_id');
    }
}
