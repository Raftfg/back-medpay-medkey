<?php

namespace Modules\Payment\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class OperationRequest extends BaseRequest
{

    protected $entite = "Operation"; //Nom de l'objet, pour les permissions par exemple
    protected $nom_param_route = "Operation"; //request route parameter
    protected $nom_table_suffixe = "Operations"; //le nom de la table sans prefixe
    protected $prefixe_table = null;   //prefixe des tables du module
    protected $nom_table = null; //le nom de la table sans prefixe
    public function __construct()
    {
        parent::__construct();
        $this->prefixe_table = config('Payment.prefixe_table');
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
            // 'uuid' => [
            //     'bail',
            //     'nullable',
            //     'string',
            //     'max:36',  // La longueur maximale d'un UUID est généralement de 36 caractères.
            // ],
            // 'user_id' => [
            //     'bail',
            //     'nullable',
            //     'exists:Modules\Acl\Entities\User,uuid',
            // ],
            'is_synced' => [
                'bail',
                'nullable',
                'boolean',
            ],
            // 'movement_id' => [
            //     'bail',
            //     'nullable',
            //     'exists:Modules\Movment\Entities\Movment,uuid',
            // ],
            // 'payment_method_id' => [
            //     'bail',
            //     'nullable',
            //     'integer',
            // ],
            // 'cash_register_id' => [
            //     'bail',
            //     'nullable',
            //     'integer',
            //     'exists:Modules\CashRegister\Entities\CashRegister,uuid',
            // ],
            // 'number' => [
            //     'bail',
            //     'nullable',
            //     'integer',
            // ],
            // 'lastname' => [
            //     'bail',
            //     'nullable',
            //     'string',
            //     'max:255',
            // ],
            // 'firstname' => [
            //     'bail',
            //     'nullable',
            //     'string',
            //     'max:255',
            // ],
            // 'montant' => [
            //     'bail',
            //     'nullable',
            //     'integer',
            // ],
            // 'status' => [
            //     'bail',
            //     'nullable',
            //     'integer',
            //     'in:0,1,2'
            // ],
            // 'deleted_at' => [
            //     'bail',
            //     'nullable',
            //     'date',
            // ],
        ];

        return $rules;
    }
}
