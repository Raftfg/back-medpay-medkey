<?php

namespace Modules\Acl\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UserRequest extends BaseRequest {

    protected $entite = "User"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "user"; //request route parameter
    protected $nom_table_suffixe = "users"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe
    public function __construct() {
        parent::__construct();
        $this->prefixe_table = config('acl.prefixe_table');
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
                'required',
                'string',
                'max:125',
            ],
            'prenom' => [
                'bail',
                'required',
                'string',
                'max:125',
            ],
            'role_id' => [
                'bail',
                'required',
                'uuid',
                'exists:Modules\Acl\Entities\Role,uuid',
            ],
            // 'email' => [
            //     'bail',
            //     'required',
            //     'email',
            //     Rule::unique($this->nom_table),
            // ],
            'email' => [
                'bail',
                'required',
                'email',
                'unique:users,email'
            ],
            'telephone' => [
                'bail',
                'nullable',
                'string',
                'min:8',
            ],
            'sexe' => [
                'bail',
                'required',
                'string',
                
            ],
            'adresse' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'password' => [
                'bail',
                'required',
                'string',
                'min:8',
            ],
            'confirm_password' => [
                'bail',
                'required',
                'string',
                'same:password',
                'min:8',
            ],
            'adresse_code_civic' => [
                'sometimes',
                'nullable',
                'string',
                'max:10',
            ],
            'adresse_rue' => [
                'sometimes',
                'nullable',
                'string',
                'max:125',
            ],
            'adresse_apt' => [
                'sometimes',
                'nullable',
                'string',
                'max:10',
            ],
            'adresse_code_postal' => [
                'sometimes',
                'nullable',
                'string',
                'max:10',
            ],
            'fax' => $this->telephobeRules(),
            'tel' => $this->telephobeRules(),
            'tel_mobile' => $this->telephobeRules(),
        ];
        return $rules;
    }

}
