<?php

namespace Modules\Stock\Http\Requests;

use Illuminate\Validation\Rule;

class SupplierUpdateRequest extends SupplierRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        // return user_api()->isPermission("update $this->entite");
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $uuid = request()->route($this->nom_param_route);
        $rules = $this->reglesCommunes();

        $rules['email'] = [
            'bail',
            'nullable',
            'email',
            'min:5',
            'max:255',
            Rule::unique($this->nom_table)->ignore($uuid, 'uuid'),
        ];
        $rules['phone_number'] = [
            'bail',
            'required',
            'integer',
            'min:10000000',
            'max:99999999999999999999',
            Rule::unique($this->nom_table)->ignore($uuid, 'uuid'),
        ];
        
        return $rules;
    }

}
