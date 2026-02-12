<?php

namespace Modules\Stock\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class SupplyRequest extends BaseRequest {

    protected $entite = "Supply"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "supply"; //request route parameter
    protected $nom_table_suffixe = "supplies"; //le nom de la table sans prefixe
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
            'total' => [
                'bail',
                'required',
                'numeric',
                'min:0',
                'max:999999999999999.99',
            ],
            'stock_id' => [
                'bail',
                'required',
                'uuid',
                'exists:Modules\Stock\Entities\Stock,uuid',
            ],
     
            'supplyProducts.*.supplier_id' => [
                'bail',
                'required',
                'uuid',
                'exists:Modules\Stock\Entities\Supplier,uuid',
            ],
            'supplyProducts.*.product_id' => [
                'bail',
                'required',
                'uuid',
                'exists:Modules\Stock\Entities\Product,uuid',
            ],
            'supplyProducts.*.units_per_box' => [
                'bail',
                'required',
                'integer',
                'min:1',
                'max:999999',
            ],
            'supplyProducts.*.expire_date' => [
                'bail',
                'required',
                'date',
                'after:'. $today,
            ],
            'supplyProducts.*.quantity' => [
                'bail',
                'required',
                'integer',
                'min:1',
                'max:999999999',
            ],
            'supplyProducts.*.purchase_price' => [
                'bail',
                'required',
                'numeric',
                'min:0',
                'max:999999999999',
            ],
            'supplyProducts.*.profit_margin' => [
                'bail',
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
            'supplyProducts.*.lot_number' => [
                'bail',
                'required',
                'string',
                'min:3',
                'max:30',
            ],
        ];
        return $rules;
    }
}
