<?php

namespace Modules\Acl\Http\Controllers\Api\V1;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserCurrentResource;
use Modules\Acl\Http\Requests\RegisterStoreRequest;
use Modules\Acl\Repositories\RoleRepository;
use Modules\Acl\Repositories\UserRepository;
use Hash;

class RegisterController extends \Modules\Acl\Http\Controllers\AclController {
    use \Modules\Acl\Traits\EnvoiNotificationUserTrait;
    
    /**
     * @var PostRepository
     */
    protected $userRepository, $roleRepository;
    
    public function __construct(UserRepository $userRepository, RoleRepository $roleRepository) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }
    
    
   /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(RegisterStoreRequest $request)
    {
        $attributs = $request->all();
        $item = DB::transaction(function () use ($attributs) {
            $role = $this->roleRepository->client();
            $attributs['password'] = Hash::make($attributs['password']);
            $item = $this->userRepository->create($attributs);
            $item->assignRole($role->name);
            
            return $item;
        });
        $item = $item->fresh();
        
        $this->confirmationCourriel($item);
        
        return new UserCurrentResource($item);
    } 
    
}
