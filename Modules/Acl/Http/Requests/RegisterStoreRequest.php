<?php

namespace Modules\Acl\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class RegisterStoreRequest extends UserRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $rules = [
            'email' => [
                'bail',
                'required',
                'email',
                Rule::unique($this->nom_table),
            ],
            'password' => [
                'bail',
                'required',
                'string',
                'min:8',
            ],
            'confirm_password' => [
                'bail',
                'required',
                'string',
                'same:password',
                'min:8',
            ],
        ];
        return $rules;
    }

}
