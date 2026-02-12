<?php

namespace Modules\Absence\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class VacationRequest extends BaseRequest {

    protected $entite = "Vacation"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "vacation"; //request route parameter
    protected $nom_table_suffixe = "vacations"; //le nom de la table sans prefixe
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
            'start_date' => [
                'bail',
                'nullable',
                'date',
            ],
            'end_date' => [
                'bail',
                'nullable',
                'date',
            ],
            'status' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'note' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'motif_urgence' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'departments_id' => [
                'bail',
                'nullable',
                'exists:Modules\Acl\Entities\Department,uuid',
            ],
            'reject_reason' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'decision_chief' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'pathFile' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'users_id' => [
                'bail',
                'required',
                'exists:Modules\Acl\Entities\User,uuid',
            ],
            'type_vacations_id' => [
                'bail',
                'required',
                'exists:Modules\Absence\Entities\TypeVacation,uuid',
            ],
        ];
        return $rules;
    }

}
