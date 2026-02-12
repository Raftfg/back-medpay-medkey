<?php

namespace Modules\Stock\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StockProductRequest extends BaseRequest {

    protected $entite = "StockProduct"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "stock_product"; //request route parameter
    protected $nom_table_suffixe = "stock_products"; //le nom de la table sans prefixe
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
                'exists:Modules\Stock\Entities\Product,id',
            ],
            'stock_id' => [
                'bail',
                'required',
                'uuid',
                'exists:Modules\Stock\Entities\Stock,id',
            ],
            'lot_number' => [
                'bail',
                'required',
                'string',
                'min:5',
                'max:25',
            ],
            'units_per_box' => [
                'bail',
                'required',
                'integer',
                'min:1',
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
            ],
            'purchase_price' => [
                'bail',
                'required',
                'numeric',
                'min:0',
            ],
            'selling_price' => [
                'bail',
                'required',
                'numeric',
                'min:0',
            ],
        ];
        return $rules;
    }

}
