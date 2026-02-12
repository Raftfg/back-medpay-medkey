<?php

namespace Modules\Acl\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class RoleStoreRequest extends RoleRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // public function authorize() {
    //     return user_api()->isPermission("create $this->entite");
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $rules = [
            'display_name' => [
                'bail',
                'nullable',
                // 'required',
                'string',
                Rule::unique($this->nom_table),
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