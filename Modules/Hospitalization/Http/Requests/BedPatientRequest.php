<?php

namespace Modules\Hospitalization\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class BedPatientRequest extends BaseRequest {

    protected $entite = "BedPatient"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "bed_patient"; //request route parameter
    protected $nom_table_suffixe = "bed_patients"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe

    public function __construct() 
    {
        parent::__construct();
        $this->prefixe_table = config('stock.prefixe_table');
        $this->nom_table = $this->prefixe_table.$this->nom_table_suffixe;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function reglesCommunes() 
    {
        $today = now()->format('Y-m-d');

        $rules = [
            'comment' => [
                'bail',
                'nullable',
                'string',
                'min:3',
                'max:255',
            ],
            'start_occupation_date' => [
                'bail',
                'required',
                'date',
                // 'before_or_equal:' . $today,
            ],
            'number_of_days' => [
                'bail',
                'required',
                'integer',
                'min:1',
                'max:999999',
            ],
            'bed_id' => [
                'bail',
                'required',
                'uuid',
                'exists:Modules\Hospitalization\Entities\Bed,uuid',
            ],
            'patient_id' => [
                'bail',
                'required',
                // 'uuid',
                // 'exists:Modules\Patient\Entities\Patiente,uuid',
            ],
        ];
        return $rules;
    }
}
