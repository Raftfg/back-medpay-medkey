<?php

namespace Modules\Acl\Http\Requests;

use Illuminate\Validation\Rule;

class ProfilUpdateRequest extends UserRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return request()->uuid == user_api()->uuid;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $uuid = user_api()->uuid;
        $rules = $this->reglesCommunes();
        unset($rules['role_id']);
        $rules['email'] = [
            'bail',
            'required',
            'email',
            Rule::unique($this->nom_table)->ignore($uuid, 'uuid'),
        ];
        $rules['password'] = [
            'sometimes',
            'nullable',
            'string',
            'min:8',
        ];
        $rules['confirm_password'] = [
            'sometimes',
            'nullable',
            'string',
            'same:password',
            'min:8',
        ];
        $rules['tel_mobile'] = [
            'bail',
            'required',
            'digits_between:10,10',
            Rule::unique($this->nom_table)->ignore($uuid, 'uuid'),
        ];
        return $rules;
    }

}
