<?php

namespace Modules\Acl\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UserEmailConfirmationRequest extends BaseRequest {

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
            'uuid' => [
                'bail',
                'required',
                'uuid',
                'exists:Modules\Acl\Entities\User,uuid'
            ],
        ];
        return $rules;
    }

    public function validationData() {
        $data = parent::validationData();
        $data['uuid'] = $this->route('uuid');
        return $data;
    }

}
