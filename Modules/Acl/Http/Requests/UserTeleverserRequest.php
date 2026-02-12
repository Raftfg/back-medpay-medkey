<?php

namespace Modules\Acl\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;
use Modules\Acl\Http\Requests\UserRequest;

class UserTeleverserRequest extends UserRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        // return user_api()->uuid == request()->route('uuid');//user_api()->isPermission("update $this->entite");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $rules = [];
        $rules['documents'] = $this->mediaRulesArray();
        $rules['documents.*'] = $this->mediaImageRules();
        return $rules;
    }

}
