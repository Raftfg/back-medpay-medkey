<?php

namespace Modules\Acl\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UserStoreRequest extends UserRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
        // return user_api()->isPermission("create $this->entite");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    // public function rules()
    // {
    //     // $rules = $this->reglesCommunes();
    //     $rules = [];
    //     return $rules;
    // }

    // public function rules() {
    //     $rules = $this->reglesCommunes();
    //     return $rules;
    // }

    public function rules() {
        $uuid = request()->route($this->nom_param_route);

        $rules = [
            'name' => [
                'bail',
                'required',
                'string',
                'max:125',
            ],
            'prenom' => [
                'bail',
                'required',
                'string',
                'max:125',
            ],
            'role_id' => [
                'bail',
                'required',
                'uuid',
                'exists:Modules\Acl\Entities\Role,uuid',
            ],
            'email' => [
                'bail',
                'required',
                'email',
                'unique:users,email'
            ],
            'telephone' => [
                'bail',
                'nullable',
                'string',
                'min:8',
            ],
            'sexe' => [
                'bail',
                'required',
                'string',
             
            ],
            'adresse' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],
  
            'fax' => $this->telephobeRules(),
            'tel' => $this->telephobeRules(),
            'tel_mobile' => $this->telephobeRules(),
        ];

     
        return $rules;
    }
}
