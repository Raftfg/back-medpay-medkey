<?php

namespace Modules\Acl\Http\Requests;

use Illuminate\Validation\Rule;

class ProfilShowRequest extends UserRequest {

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
        $rules = [];
        return $rules;
    }

}
