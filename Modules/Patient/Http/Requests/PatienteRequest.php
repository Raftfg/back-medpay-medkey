<?php

namespace Modules\Patient\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class PatienteRequest extends BaseRequest
{

    protected $entite = "Patiente"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "patiente"; //request route parameter
    protected $nom_table_suffixe = "patients"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe
    public function __construct()
    {
        parent::__construct();
        $this->prefixe_table = config('patiente.prefixe_table');
        $this->nom_table = $this->prefixe_table . $this->nom_table_suffixe;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function reglesCommunes()
    {
        $rules = array_merge(parent::multiTenantRules(), [
            // 'ipp' => [
            //     'bail',
            //     'nullable',
            //     'integer',
            //     'max:12',
            // ],
            // 'uuid' => [
            //     'bail',
            //     'nullable',
            //     'string',
            //     'max:255',
            // ],
            'lastname' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            // 'is_synced' => [
            //     'nullable',

            // ],
            // 'deleted_at' => [
            //     'nullable',

            // ],
            // 'users_id',
            // 'users_id' => [
            //     'bail',
            //     'required',
            //     // 'exists:Modules\Acl\Entities\User,id',
            //     'exists:Modules\Acl\Entities\User,uuid',
            // ],

            'firstname' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'date_birth' => [
                'bail',
                'required',
                'date',
                'before:today',
            ],
            'age' => [
                'bail',
                'nullable',
                'integer',
                // 'max:100',
            ],
            'maison' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'phone' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'nom_marital' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'email' => [
                'bail',
                'email',
                'nullable',
                'string',
                'max:255',
            ],
            'whatsapp' => [
                'bail',
                'nullable',
                'string',
                // 'max:8',
            ],
            'profession' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'gender' => [
                'bail',
                'required',
                'string',
                'max:255',
            ],
            'emergency_contac' => [
                'bail',
                'required',
                'string',
                // 'max:100',
            ],
            'marital_status' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'autre' => [
                'bail',
                'nullable',
                'string',
            ],
            'date_deces' => [
                'bail',
                'nullable',
                'date',
            ],
            'code_postal' => [
                'bail',
                'nullable',
                'string',

            ],
            'pays_id' => [
                'bail',
                'nullable',
                'exists:Modules\Administration\Entities\Pays,id',
            ],
            'departements_id' => [
                'bail',
                'nullable',
                'exists:Modules\Administration\Entities\Departement,id',
            ],
            'communes_id' => [
                'bail',
                'nullable',
                'exists:Modules\Administration\Entities\Commune,id',
            ],
            'arrondissements_id' => [
                'bail',
                'nullable',
                'exists:Modules\Administration\Entities\Arrondissement,id',
            ],
            'quartier' => [
                'bail',
                'string',
                'nullable',
            ],
        ]);
        return $rules;
    }
}
