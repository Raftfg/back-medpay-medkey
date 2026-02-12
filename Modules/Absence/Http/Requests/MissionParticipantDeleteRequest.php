<?php

namespace Modules\Absence\Http\Requests;

class MissionParticipantDeleteRequest extends MissionParticipantRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        // return user_api()->isPermission("delete $this->entite");
        return true;
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
