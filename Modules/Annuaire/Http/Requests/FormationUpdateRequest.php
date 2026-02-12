<?php

namespace Modules\Annuaire\Http\Requests;

use Illuminate\Validation\Rule;

class FormationUpdateRequest extends FormationRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return user_api()->isPermission("update $this->entite");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $uuid = request()->route($this->nom_param_route);
        $rules = $this->regelesCommunes();
        // $rules['code'] = [            
        //     'bail',
        //     'required',
        //     'string',
        //     'max:125',
        //     Rule::unique($this->nom_table)->ignore($uuid, 'uuid')
        // ];
        return $rules;
    }

}
