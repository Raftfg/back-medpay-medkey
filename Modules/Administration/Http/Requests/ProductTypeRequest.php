<?php

namespace Modules\Administration\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ProductTypeRequest extends BaseRequest
{

    protected $entite = "ProductType"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "productType"; //request route parameter
    protected $nom_table_suffixe = "productTypes"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe
    public function __construct()
    {
        parent::__construct();
        $this->prefixe_table = config('productType.prefixe_table');
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
            'designation' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
            'description' => [
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
        ];
        return $rules;
    }
}
