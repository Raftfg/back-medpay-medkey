<?php

namespace Modules\Media\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class MediaRequest extends BaseRequest {

    protected $entite = "Media"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "media"; //request route parameter
    protected $nom_table_suffixe = "medias"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe
    public function __construct() {
        parent::__construct();
        $this->prefixe_table = config('media.prefixe_table');
        $this->nom_table = $this->prefixe_table.$this->nom_table_suffixe;
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
