<?php

namespace Modules\Movment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Illuminate\Support\Facades\DB;

use Modules\Patient\Entities\Patiente;
use Modules\Administration\Entities\MedicalAct;
use Modules\Administration\Entities\Service;
use Modules\Movment\Entities\Movment;

use Modules\Movment\Entities\Allergie;
use Modules\Movment\Entities\Antecedent;
use Modules\Movment\Entities\Livestyle;
use Modules\Movment\Entities\Measurement;

use Modules\Movment\Http\Controllers\ReportLogics;


class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getPatientSats()
    {

        $data = [
            "totalPatient" => Patiente::get()->count(),
            "totalPatient_F" => Patiente::where('gender','Feminin')->count(),
            "totalPatient_H" => Patiente::where('gender','Masculin')->count(),
            "totalVenue" => Movment::get()->count(),
            "totalVenue_In" => Movment::whereNull('releasedate')->count(),
            "totalVenue_Out" => Movment::whereNotNull('releasedate')->count(),
            "totalVenue_Child" => Patiente::where('age', '<',18)->count(),
            "totalVenue_Older" =>  Patiente::where('age', '>=',18)->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Patients dashbord Stat.'
        ]);
    }


    public function getServicesSats()
    {
        $data = [
            "urgencePatient" => ReportLogics::getMovmentByService('urgence'),
            "consultationPatient" => ReportLogics::getMovmentByService('consultation'),
            "infirmeriePatient" => ReportLogics::getMovmentByService('infirmerie'),
            "chirurgiePatient" => ReportLogics::getMovmentByService('chirurgie'),
            "laboratoirePatient" => ReportLogics::getMovmentByService('laboratoire'),
            "imageriePatient" => ReportLogics::getMovmentByService('imagerie'),
            "pediatriePatient" => ReportLogics::getMovmentByService('pediatrie'),
            "maternitePatient" =>  ReportLogics::getMovmentByService('maternite'),
        ];

        $DureeMoyen = [
            "urgence" => ReportLogics::serviceTreatementAbsTime('urgence'),
            "consultation" => ReportLogics::serviceTreatementAbsTime('consultation'),
            "infirmerie" => ReportLogics::serviceTreatementAbsTime('infirmerie'),
            "chirurgie" => ReportLogics::serviceTreatementAbsTime('chirurgie'),
            "laboratoire" => ReportLogics::serviceTreatementAbsTime('laboratoire'),
            "imagerie" => ReportLogics::serviceTreatementAbsTime('imagerie'),
            "pediatrie" => ReportLogics::serviceTreatementAbsTime('pediatrie'),
            "maternite" =>  ReportLogics::serviceTreatementAbsTime('maternite'),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'duree' =>  $DureeMoyen,
            'message' => 'Patients par service.'
        ]);

    }
}
