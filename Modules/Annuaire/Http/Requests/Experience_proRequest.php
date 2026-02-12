<?php

namespace Modules\Annuaire\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class Experience_proRequest extends BaseRequest {

    protected $entite = "Experience_pro"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "experience_pro"; //request route parameter
    protected $nom_table_suffixe = "experience_pros"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe
    public function __construct() {
        parent::__construct();
        $this->prefixe_table = config('annuaire.prefixe_table');
        $this->nom_table = $this->prefixe_table.$this->nom_table_suffixe;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function regelesCommunes() {
        $rules = [
            'start_date' => [
                'bail',
                'required',
                'date',
            ],
            'end_date' => [
                'bail',
                'required',
                'date',
            ],
            'place' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'job' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'users_id' => [
                'bail',
                'required',
                'exists:Modules\Acl\Entities\User,uuid',
            ],
            'missions' => [
                'bail',
                'required',
                'text',
                'max:65535',
            ],
            'document_link' => [
                'bail',
                'required',
                'text',
                'max:65535',
            ],
        ];
        return $rules;
    }

}
