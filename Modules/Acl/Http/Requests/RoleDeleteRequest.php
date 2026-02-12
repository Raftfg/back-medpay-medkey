<?php

namespace Modules\Acl\Http\Requests;
use Illuminate\Support\Facades\App;
use Illuminate\Auth\Access\AuthorizationException;
use Modules\Acl\Repositories\RoleRepository;

class RoleDeleteRequest extends RoleRequest {

    protected $roleRepositoryEloquent;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // public function authorize() {
    //     $uuid = request()->route('role');
    //     $this->roleRepositoryEloquent = App::make(RoleRepository::class);
    //     $item = $this->roleRepositoryEloquent->findByUuidOrFail($uuid)->first();
    //     return user_api()->isPermission("delete $this->entite") && 
    //              $item->isDeleted() &&
    //              !($item->permissions()->count() || $item->users()->count());
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $pt = prefixe_table();
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
