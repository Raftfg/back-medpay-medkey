<?php

namespace Modules\Stock\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends BaseRequest {

    protected $entite = "Product"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "product"; //request route parameter
    protected $nom_table_suffixe = "products"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe

    public function __construct() {
        parent::__construct();
        $this->prefixe_table = config('stock.prefixe_table');
        $this->nom_table = $this->prefixe_table.$this->nom_table_suffixe;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function reglesCommunes() {
        $rules = array_merge(parent::multiTenantRules(), [
            'name' => [
                'bail',
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'brand' => [
                'bail',
                'sometimes',
                'nullable',
                'string',
                'min:3',
                'max:255',
            ],
            'dosage' => [
                'bail',
                'sometimes',
                'nullable',
                'string',
                'min:3',
                'max:255',
            ],
            'conditioning_unit_id' => [
                'bail',
                'required',
                'uuid',
                'exists:Modules\Stock\Entities\ConditioningUnit,uuid',
            ],
            'administration_route_id' =>[
                'bail',
                'sometimes',
                'uuid',
                'exists:Modules\Stock\Entities\AdministrationRoute,uuid',
            ],
            'sale_unit_id' => [
                'bail',
                'required',
                'uuid',
                'exists:Modules\Stock\Entities\SaleUnit,uuid',
            ],
            'category_id' =>[
                'bail',
                'required',
                'uuid',
                'exists:Modules\Stock\Entities\Category,uuid',
            ],
            'type_name' =>[
                'bail',
                'required',
                'string',
                'in:Drugs,Consumables,Notebooks and cards'
            ],
        ]);
        return $rules;
    }
}
