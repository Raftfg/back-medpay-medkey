<?php

namespace Modules\Annuaire\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class CertificationRequest extends BaseRequest {

    protected $entite = "Certification"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "certification"; //request route parameter
    protected $nom_table_suffixe = "Certifications"; //le nom de la table sans prefixe
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
            'description' => [
                'bail',
                'required',
                'text',
                'max:65535',
            ],
            'date' => [
                'bail',
                'required',
                'date',
            ],
            'garant' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'document_link' => [
                'bail',
                'required',
                'text',
                'max:65535',
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
