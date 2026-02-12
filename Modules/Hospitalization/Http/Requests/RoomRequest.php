<?php

namespace Modules\Hospitalization\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class RoomRequest extends BaseRequest {

    protected $entite = "Room"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "room"; //request route parameter
    protected $nom_table_suffixe = null; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = 'rooms'; //le nom de la table sans prefixe

    public function __construct() 
    {
        parent::__construct();
        $this->prefixe_table = config('stock.prefixe_table');
        $this->nom_table = $this->prefixe_table.$this->nom_table.$this->nom_table_suffixe;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function reglesCommunes() 
    {
        $rules = [
            'name' => [
                'bail',
                'required',
                'string',
                'min:3',
                'max:30',
                Rule::unique($this->nom_table),
            ],
            'bed_capacity' => [
                'bail',
                'required',
                'integer',
                'min:1',
                'max:10', // Limite maximale de 10 lits par salle
            ],
            'price' => [
                'bail',
                'numeric',
                'min:1',
                'max:999999999',
            ],
            'description' => [
                'bail',
                'nullable',
                'string',
                'min:5',
                'max:255',
            ],
        ];
        return $rules;
    }
}
