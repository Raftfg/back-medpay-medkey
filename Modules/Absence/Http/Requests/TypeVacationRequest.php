<?php

namespace Modules\Absence\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class TypeVacationRequest extends BaseRequest {

    protected $entite = "TypeVacation"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "type_vacation"; //request route parameter
    protected $nom_table_suffixe = "type_vacations"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe
    public function __construct() {
        parent::__construct();
        $this->prefixe_table = config('absence.prefixe_table');
        $this->nom_table = $this->prefixe_table.$this->nom_table_suffixe;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function reglesCommunes() {
        $rules = [
            'code' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'libelle' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'require_certify' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
        ];
        return $rules;
    }

}
