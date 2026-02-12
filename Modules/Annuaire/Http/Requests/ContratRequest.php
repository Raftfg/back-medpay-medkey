<?php

namespace Modules\Annuaire\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ContratRequest extends BaseRequest {

    protected $entite = "Contrat"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "contrat"; //request route parameter
    protected $nom_table_suffixe = "contrats"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe
    public function __construct() {
        parent::__construct();
        $this->prefixe_table = config('contrat.prefixe_table');
        $this->nom_table = $this->prefixe_table.$this->nom_table_suffixe;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function reglesCommunes() {
        $rules = [
            'employment_type' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'salary' => [
                'bail',
                'nullable',
            
            ],
            'employment_start_date' => [
                'bail',
                'nullable',
                'date',
            ],
            'employment_end_date' => [
                'bail',
                'nullable',
                'date',
            ],
            'employee_id' => [
                'bail',
                'nullable',
                'exists:Modules\Annuaire\Entities\Employer,uuid',
            ],
           
        ];
        return $rules;
    }

}
