<?php

namespace Modules\Acl\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends BaseRequest {

    protected $entite = "Role"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "role"; //request route parameter
    protected $nom_table_suffixe = "roles"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe
    public function __construct() {
        parent::__construct();
        $this->prefixe_table = config('acl.prefixe_table');
        $this->nom_table = $this->nom_table_suffixe;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function reglesCommunes() {
        $rules = [
        ];
        return $rules;
    } 

}
