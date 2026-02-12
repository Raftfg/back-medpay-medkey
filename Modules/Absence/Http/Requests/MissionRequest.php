<?php

namespace Modules\Absence\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class MissionRequest extends BaseRequest {

    protected $entite = "Mission"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "mission"; //request route parameter
    protected $nom_table_suffixe = "missions"; //le nom de la table sans prefixe
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
            'name' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'projet' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'objet' => [
                'bail',
                'nullable',
                'string',
            ],
            'start_date' => [
                'bail',
                'date',
                'nullable',
            ],
            'end_date' => [
                'bail',
                'date',
                'nullable',
            ],
            'place' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'observation' => [
                'bail',
                'nullable',
                'mediumText',
            ],
            'reason' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'status' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'mission_head_id' => [
                'bail',
                'nullable',
                'exists:Modules\Acl\Entities\User,uuid',
            ],
            'departments_id' => [
                'bail',
                'nullable',
                'exists:Modules\Acl\Entities\Department,uuid',
            ],
        ];
        return $rules;
    }

}
