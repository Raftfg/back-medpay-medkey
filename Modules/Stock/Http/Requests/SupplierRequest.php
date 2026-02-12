<?php

namespace Modules\Stock\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends BaseRequest {

    protected $entite = "Supplier"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "supplier"; //request route parameter
    protected $nom_table_suffixe = "suppliers"; //le nom de la table sans prefixe
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
                'min:3',
                'max:125',
            ],
            'email' => [
                'bail',
                'nullable',
                'email',
                'min:5',
                'max:255',
                Rule::unique($this->nom_table),
            ],
            'dial_code' => [
                'bail',
                'required',
                'string',
                'min:1',
                'max:10',
            ],
            'phone_number' => [
                'bail',
                'required',
                'integer',
                'min:10000000',
                'max:99999999999999999999',
                Rule::unique($this->nom_table),
            ],
            'address' => [
                'bail',
                'nullable',
                'string',
                'min:5',
                'max:255',
            ],
            'profit_margin' => [
                'bail',
                'required',
                'integer',
                'min:0',
                'max:100',
            ],
        ];
        return $rules;
    }
}
