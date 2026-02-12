<?php

namespace Modules\Absence\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class AbsentRequest extends BaseRequest {

    protected $entite = "Absent"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "absent"; //request route parameter
    protected $nom_table_suffixe = "absents"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe
    public function __construct() {
        parent::__construct();
        $this->prefixe_table = config('absent.prefixe_table');
        $this->nom_table = $this->prefixe_table.$this->nom_table_suffixe;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function reglesCommunes() {
        $rules = [
            'type' => [
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
            'vacations_id' => [
                'bail',
                'nullable',
                'exists:Modules\Absence\Entities\Vacation,uuid',
            ],
            'missions_id' => [
                'bail',
                'nullable',
                'exists:Modules\Absence\Entities\Mission,uuid',
            ],
            'users_id' => [
                'bail',
                'required',
                'exists:Modules\Acl\Entities\User,uuid',
            ],
        ];
        return $rules;
    }

}
