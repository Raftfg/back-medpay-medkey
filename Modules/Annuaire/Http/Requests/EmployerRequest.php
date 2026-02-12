<?php

namespace Modules\Annuaire\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class EmployerRequest extends BaseRequest {

    protected $entite = "Employer"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "employer"; //request route parameter
    protected $nom_table_suffixe = "employers"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe
    public function __construct() {
        parent::__construct();
        $this->prefixe_table = config('employer.prefixe_table');
        $this->nom_table = $this->prefixe_table.$this->nom_table_suffixe;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function reglesCommunes() {
        $rules = [
            'first_name' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'last_name' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'address' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'email' => [
                'bail',
                'nullable',
                'email',
                'max:255',
                // Rule::unique($this->nom_table),
                Rule::unique($this->nom_table)->ignore($this->uuid, 'uuid'),
            ],
            'hire_date' => [
                'bail',
                'nullable',
                'date',
            ],
            'termination_date' => [
                'bail',
                'nullable',
                'date',
            ],
            'date_birth' => [
                'bail',
                'nullable',
                'date',
            ],
            'phone_number' => [
                'bail',
                'nullable',
                'string',
                'max:125',
                Rule::unique($this->nom_table)->ignore($this->uuid, 'uuid'),
            ],
            'urgency_phone' => [
                'bail',
                'nullable',
                'string',
                'max:125',
            ],
            'social_security_number' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'sex' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'employment_status' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'birthplace' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'marital_status' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'father_name' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'mother_name' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'charge' => [
                'bail',
                'nullable',
                'max:255',
            ],
            'urgency_name' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],

            'nationality' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'contract_lenght' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'work_time' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'contract_type' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'salary' => [
                'bail',
                'nullable',
            ],
            'function' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'ifu' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'npi' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'motif' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'services_id' => [
                'bail',
                'nullable',
                'exists:Modules\Administration\Entities\Service,uuid',
            ],
            'departments_id' => [
                'bail',
                'nullable',
                'exists:Modules\Administration\Entities\Department,uuid',
            ],
        ];
        return $rules;
    }

}
