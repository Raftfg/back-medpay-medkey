<?php

namespace Modules\Acl\Http\Requests;

use Illuminate\Validation\Rule;

class UserUpdateRequest extends UserRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // public function authorize() {
    //     // return user_api()->isPermission("update $this->entite");
    // }

    // public function authorize() {
    //     // return user_api()->isPermission("delete $this->entite");
    //     return true;
    // }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
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
            // 'email' => [
            //     'bail',
            //     'required',
            //     'email',
            //     Rule::unique($this->nom_table)->ignore($uuid, 'uuid'),
            // ],

            'email' => [
                'bail',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($uuid, 'uuid'), // Remplacez $uuid par l'UUID de l'utilisateur à ignorer
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
