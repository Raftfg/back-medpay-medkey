<?php

namespace Modules\Acl\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rules;

class ResetPasswordRequest extends BaseRequest {

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
        //\Log::info(request()->all());
        return [
            'token' => [
                'required'
            ],
            'email' => [
                'required',
                'email'
            ],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults()
                ],
        ];
    }

}
