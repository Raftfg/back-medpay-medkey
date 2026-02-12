<?php

namespace Modules\Acl\Http\Requests;

class UserIndexRequest extends UserRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // public function authorize() {
    //     return user_api()->isPermission("read $this->entite");
    // }
    public function authorize() {
        // $user = user_api(); // Utilisez la méthode auth() pour récupérer l'utilisateur actuel
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
