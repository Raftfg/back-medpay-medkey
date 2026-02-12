<?php

namespace Modules\Administration\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class InsuranceRequest extends BaseRequest
{

    protected $entite = "Insurance"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "insurance"; //request route parameter
    protected $nom_table_suffixe = "insurances"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe
    public function __construct()
    {
        parent::__construct();
        $this->prefixe_table = config('insurance.prefixe_table');
        $this->nom_table = $this->prefixe_table . $this->nom_table_suffixe;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function reglesCommunes()
    {
        $rules = [
            'name' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            // 'insuranceComp' => [
            //     'bail',
            //     'nullable',
            //     'string',
            //     'max:255',
            // ],

            // 'number_insurance' => [
            //     'bail',
            //     'nullable',
            //     'string',
            //     'max:255',
            // ],
            // 'is_convention' => [
            //     'bail',
            //     'nullable',
            //     'boolean',
            // ],
            // 'phone' => [
            //     'bail',
            //     'nullable',
            //     'integer',
               
            // ],


            // 'users_id' => [
            //     'bail',
            //     'required',
            //     'exists:Modules\Acl\Entities\User,uuid',
            // ],
        ];
        return $rules;
    }
}
