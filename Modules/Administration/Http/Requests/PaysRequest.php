<?php

namespace Modules\Administration\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class PaysRequest extends BaseRequest
{

    protected $entite = "Pays"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "pays"; //request route parameter
    protected $nom_table_suffixe = "payss"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe
    public function __construct()
    {
        parent::__construct();
        $this->prefixe_table = config('pays.prefixe_table');
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
            'nom' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
        ];
        return $rules;
    }
}
