<?php

namespace Modules\Stock\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StockRequest extends BaseRequest {

    protected $entite = "Stock"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "category"; //request route parameter
    protected $nom_table_suffixe = "categories"; //le nom de la table sans prefixe
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
            'name' => [
                'bail',
                'required',
                'string',
                'min:5',
                'max:125',
                Rule::unique($this->nom_table)
            ],
            'store_id' => [
                'bail',
                'required',
                'uuid',
                'exists:Modules\Stock\Entities\Store,uuid',
            ],
            // 'user_id' => [
            //     'required',
            //     'uuid',
            //     'exists:Modules\User\Entities\User,uuid',
            // ],
        ];
        return $rules;
    }

}
