<?php

namespace Modules\Hospitalization\Http\Requests;

class BedPatientIndexRequest extends BedPatientRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() 
    {
        // return user_api()->isPermission("read $this->entite");
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() 
    {
        $rules = [];
        return $rules;
    }
}
