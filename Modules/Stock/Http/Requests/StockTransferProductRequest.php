<?php

namespace Modules\Stock\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StockTransferProductRequest extends BaseRequest {

    protected $entite = "StockTransferProduct"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "stock_transfer_product"; //request route parameter
    protected $nom_table_suffixe = "stock_transfer_products"; //le nom de la table sans prefixe
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
        $rules = [
            'quantity_transfered' => [
                'bail',
                'required',
                'integer',
                'min:1',
                'max:999999999',
            ],
            'stock_transfer_id' => [
                'bail',
                'required',
                'integer',
                // 'exists:Modules\Stock\Entities\StockTransfer,id',
            ],
            'stock_product_id' => [
                'bail',
                'required',
                'integer',
                // 'exists:Modules\Stock\Entities\StockProduct,id',
            ],
        ];

        // Add unique rule for the combination of stock_transfer_id and stock_product_id
        if ($this->isMethod('post')) { 
            $rules['stock_transfer_id'][] = Rule::unique($this->nom_table)->where(function ($query) {
                return $query->where('stock_product_id', $this->input('stock_product_id'));
            });
        }
        return $rules;
    }
}
