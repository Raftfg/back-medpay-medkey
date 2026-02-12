<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('loadJsonData')) {
  function loadJsonData($name, $callback = null)
  {
    try {
      if (!str_ends_with($name, '.json')) $name = "$name.json";

      $data = file_get_contents(base_path("Modules/seedData/$name"));
      $data =  json_decode($data);
      return  $data;
      if ($callback && is_callable($callback)) {
        $data =   $callback($data);
      }
      return $data;

    } catch (\Exception $e) {
      return null;
    }
  }
}


if(!function_exists('format_money')){
  function format_money($p){
    //return retourne au format francais les montants;
   return number_format($p, 0, '.', ' ');
 }
}


if(!function_exists('format_percent')){
  function format_percent($p){
    //return retourne au format francais les montants;
   return number_format($p, 2, '.', '');
 }
}


if(!function_exists('convertToEnglishDate')){
  function convertToEnglishDate($s){
    if(!is_null($s) || !empty($s)){
      $date = strtotime($s);
      return  date('Y-m-d H:i', $date);
    }else{
      return $s;
    }
  }
}


if(!function_exists('convertToFrenchDate')){
  function convertToFrenchDate($s){
    if(!is_null($s) || !empty($s)){
      $date = strtotime($s);
      return date('d-m-Y H:i', $date);
    }else{
      return $s;
    }

  }
}


if(!function_exists('urgencesGravites')){
  function urgencesGravites($code){

     if($code =="SECONDAIRE"){
       return array("2A","2B");
    }

    if($code =="TERTIAIRE"){
       return array("3A","3B");
    }

    if($code =="PRIMAIRE"){
       return array("1A","1B","1C");
    }
}
}


if(!function_exists('urgencesCategories')){
  function urgencesCategories($code = null){

    $categories = array(
      "PRIMAIRE" => array("1A"=>"1A","1B"=>"1B","1C"=>"1C"),

      "SECONDAIRE" => ["2A","2B"],

      "TERTIAIRE" =>  array("3A","3B")
    );

    if($code)
      return $categories[$code];
    else
      return $categories;
  }
}




if(!function_exists('getPatientIdByUuid')){
  function getPatientIdByUuid($patient_uuid){
   return  DB::table('patients')->where('uuid', $patient_uuid)->first()->id;
 }
}
