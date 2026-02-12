<?php

namespace Modules\Stock\Http\Requests;

use Illuminate\Validation\Rule;

class TypeProductStoreRequest extends TypeProductRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        // return user_api()->isPermission("create $this->entite");
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $rules = $this->reglesCommunes();

        $rules['name'] = [
            'bail',
            'required',
            'string',
            'max:255',
            Rule::unique($this->nom_table)
        ];

        return $rules;
    }

}
