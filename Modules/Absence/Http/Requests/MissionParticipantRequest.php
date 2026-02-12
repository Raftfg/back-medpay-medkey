<?php

namespace Modules\Absence\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class MissionParticipantRequest extends BaseRequest {

    protected $entite = "MissionParticipants"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "missions_participant"; //request route parameter
    protected $nom_table_suffixe = "missions_participants"; //le nom de la table sans prefixe
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
            'institutions' => [
                'bail',
                'nullable',
                'string',
            ],
            'missions_id' => [
                'bail',
                'nullable',
                'exists:Modules\Absence\Entities\Mission,uuid',
            ],
            'users_id' => [
                'bail',
                'nullable',
                'exists:Modules\Acl\Entities\User,uuid',
            ],
        ];
        return $rules;
    }

}
