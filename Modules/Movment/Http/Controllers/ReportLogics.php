<?php

namespace Modules\Movment\Http\Controllers;

use Illuminate\Support\Facades\DB;

use Modules\Patient\Entities\Patiente;
use Modules\Administration\Entities\MedicalAct;
use Modules\Administration\Entities\Service;
use Modules\Movment\Entities\Movment;

use Modules\Movment\Entities\Allergie;
use Modules\Movment\Entities\Antecedent;
use Modules\Movment\Entities\Livestyle;
use Modules\Movment\Entities\Measurement;

use Carbon\Carbon;

class ReportLogics
{

  /** mouvement par services en cours de traimenet  */
  public static function getMovmentByService($code)
  {
   return Movment::whereNull('releasedate')->where('active_services_id', self::getServiceIdByCode($code))->count();
 }

 /** services Id par code   */

 public static function getServiceIdByCode($code)
 {
  return Service::where('code',$code)->first()->id;
}

/** Calcul de traiment */

public static function serviceTreatementAbsTime($code)
{
 $CompletedMvt =  Movment::whereNotNull('releasedate')->where('active_services_id', self::getServiceIdByCode($code))->get();

 $dureeArray = [];
 foreach ($CompletedMvt as $Mvt) {
  $diff = Carbon::parse( $Mvt->arrivaldate )->diffInDays( $Mvt->releasedate );
  array_push($dureeArray, $diff);
}

if(count($dureeArray) > 0){
 $average =  array_sum($dureeArray)/count($dureeArray);
}else{
  $average = 0 ;
}

return $average ;

}



}
