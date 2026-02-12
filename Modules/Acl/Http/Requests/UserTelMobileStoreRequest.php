<?php

namespace Modules\Acl\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UserTelMobileStoreRequest extends BaseRequest {

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
            'tel_mobile' => [
                'bail',
                'required',
                'digits_between:10,10',
                Rule::unique('users')->ignore($uuid, 'uuid'),
            ],
        ];
        return $rules;
    }

}
