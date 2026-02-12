<?php

namespace Modules\Notifier\Http\Requests;

class NotifierTrackingIndexRequest extends NotifierTrackingRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return 
                user_api()->isPermission("read $this->entite");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $rules = [
        ];
        return $rules;
    }

}
