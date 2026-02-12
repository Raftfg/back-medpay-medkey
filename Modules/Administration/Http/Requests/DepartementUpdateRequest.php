<?php

namespace Modules\Administration\Http\Requests;

use Illuminate\Validation\Rule;

class DepartementUpdateRequest extends DepartementRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return user_api()->isPermission("update $this->entite");
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $uuid = request()->route($this->nom_param_route);
        $rules = $this->reglesCommunes();
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
