<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UuidValidateRequest extends BaseRequest {

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
            ],
        ];
        return $rules;
    }

    public function validationData() {
        $parameterName = request()->route()->parameterNames[0];
        $data = parent::validationData();
        $data['uuid'] = $this->route($parameterName);
        return $data;
    }

}
