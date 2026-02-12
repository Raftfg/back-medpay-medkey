<?php

namespace Modules\Acl\Http\Requests;
use Illuminate\Support\Facades\App;
use Illuminate\Auth\Access\AuthorizationException;
use Modules\Acl\Repositories\UserRepository;

class UserDeleteRequest extends UserRequest {

    protected $userRepositoryEloquent;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // public function authorize() {
    //     // $uuid = request()->route($this->nom_param_route);
    //     // $this->userRepositoryEloquent = App::make(UserRepository::class);
    //     // $item = $this->userRepositoryEloquent->findByUuidOrFail($uuid)->first();
    //     // return user_api()->isPermission("delete $this->entite") && !$item->acteur;
    // }
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

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException($this->erreurMessageSuppression);
    }

}
