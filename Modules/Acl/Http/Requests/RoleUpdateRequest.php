<?php

namespace Modules\Acl\Http\Requests;

use Illuminate\Validation\Rule;

class RoleUpdateRequest extends RoleRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // public function authorize() {
    //     return user_api()->isPermission("update $this->entite");
    // }
    public function authorize() {
        // return user_api()->isPermission("delete $this->entite");
        return true;
    }


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
                Rule::unique($this->nom_table)->ignore($uuid, 'uuid')
            ],
            'display_name' => [
                'bail',
                'sometimes',
                'string',
                Rule::unique($this->nom_table)->ignore($uuid, 'uuid')
            ],
            'permissions' => [
                'bail',
                'sometimes',
                'nullable',
                'array',
                //'min:0',
            ],
            'permissions.*' => [
                'bail',
                'sometimes',
                'nullable',
                'uuid',
                'exists:Modules\Acl\Entities\Permission,uuid',
            ],
        ];
        return $rules;
    }

}
