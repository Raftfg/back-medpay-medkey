<?php

namespace Modules\Stock\Http\Requests;

use Illuminate\Validation\Rule;

class AdministrationRouteUpdateRequest extends AdministrationRouteRequest {

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
        $rules['name'] = [
            'bail',
            'required',
            'string',
            'min:3',
            'max:125',
            Rule::unique($this->nom_table)->ignore($uuid, 'uuid'),
        ];
        
        return $rules;
    }
}
