<?php

namespace Modules\Annuaire\Http\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class Experience_proStoreRequest extends Experience_proRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return user_api()->isPermission("create $this->entite");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $rules = $this->regelesCommunes();
        return $rules;
    }

}
