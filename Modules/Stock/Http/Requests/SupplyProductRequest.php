<?php

namespace Modules\Stock\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class SupplyProductRequest extends BaseRequest {

    protected $entite = "SupplyProduct"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "supply_product"; //request route parameter
    protected $nom_table_suffixe = "supply_products"; //le nom de la table sans prefixe
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
        $today = now()->format('Y-m-d');

        $rules = [
            'product_id' => [
                'bail',
                'required',
                'uuid',
                'exists:Modules\Stock\Entities\Product,uuid',
            ],
            'supply_id' => [
                'bail',
                'required',
                'uuid',
                'exists:Modules\Stock\Entities\Supply,uuid',
            ],
            'supplier_id' => [
                'bail',
                'required',
                'uuid',
                'exists:Modules\Stock\Entities\Supplier,uuid',
            ],
            'units_per_box' => [
                'bail',
                'required',
                'integer',
                'min:1',
                'max:999999',
            ],
            'expire_date' => [
                'bail',
                'required',
                'date',
                'after:'. $today,
            ],
            'quantity' => [
                'bail',
                'required',
                'integer',
                'min:1',
                'max:999999999',
            ],
            'purchase_price' => [
                'bail',
                'required',
                'numeric',
                'min:0',
                'max:999999999999',
            ],
            'profit_margin' => [
                'bail',
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
            'lot_number' => [
                'bail',
                'required',
                'string',
                'min:3',
                'max:30',
                Rule::unique($this->nom_table)->where(function ($query) {
                    return $query->where('supply_id', $this->route($this->nom_param_route));
                }),
            ],
        ];
        return $rules;
    }
}
