<?php

namespace Modules\Patient\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class PatientInsuranceRequest extends BaseRequest
{

    protected $entite = "PatientInsurance"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "patientInsurance"; //request route parameter
    protected $nom_table_suffixe = "patient_insurances"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe
    public function __construct()
    {
        parent::__construct();
        $this->prefixe_table = config('patient.prefixe_table');
        $this->nom_table = $this->prefixe_table . $this->nom_table_suffixe;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function reglesCommunes()
    {
        $rules = [
            // 'date_debut' => [
            //     'bail',
            //     'nullable',
            //     'date',
            //     'max:255',
            // ],
            // 'date_fin' => [
            //     'bail',
            //     'nullable',
            //     'date',
            //     'max:255',
            // ],
            // 'observation' => [
            //     'bail',
            //     'nullable',
            //     'string',
            //     'max:255',
            // ],
            // 'numero_police' => [
            //     'bail',
            //     'nullable',
            //     'string',
            //     'max:20', // ou ajustez la longueur maximale selon vos besoins
            // ],
            'patients_id' => [
                'bail',
                'required',
                'exists:Modules\Patient\Entities\Patiente,uuid',
            ],
            // 'pack_id' => [
            //     'bail',
            //     'required',
            //     'nullable',
            //     'exists:Modules\Administration\Entities\Pack,uuid',
            // ],
        ];
        return $rules;
    }
}
