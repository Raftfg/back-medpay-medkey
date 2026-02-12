<?php

namespace Modules\Administration\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class PackRequest extends BaseRequest
{

    protected $entite = "Pack"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "pack"; //request route parameter
    protected $nom_table_suffixe = "packs"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe
    public function __construct()
    {
        parent::__construct();
        $this->prefixe_table = config('pack.prefixe_table');
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
            // 'percentage' => [
            //     'bail',
            //     'nullable',
            //     'decimal',
            // ],
            'percentage' => [
                'bail',
                'nullable',
                'numeric', // Utiliser 'numeric' au lieu de 'decimal'
                'between:0,100', // Ajouter une règle pour garantir que la valeur est entre 0 et 100
            ],

            'insurances_id' => [
                'bail',
                'required',
                'exists:Modules\Administration\Entities\Insurance,uuid',
            ],
            // 'product_types_id' => [
            //     'bail',
            //     'required',
            //     'exists:Modules\Administration\Entities\ProductType,uuid',
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
