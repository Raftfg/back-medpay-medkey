<?php

namespace Modules\Acl\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UserTelMobileVerifierRequest extends BaseRequest {

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
        $uuid = user_api()->uuid;
        $rules = [
            'tel_mobile_code' => [
                'bail',
                'required',
                'digits_between:6,6',
                Rule::in([user_api()->tel_mobile_code]),
            ],
        ];
        return $rules;
    }

}
