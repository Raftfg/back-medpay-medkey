<?php

namespace Modules\Acl\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Acl\Entities\Permission;
use App\Http\Controllers\Api\V1\ApiController;
use Modules\Acl\Repositories\PermissionRepository;
use Modules\Acl\Http\Resources\PermissionsResource;

class PermissionController extends \Modules\Acl\Http\Controllers\AclController {

    /**
     * @var PostRepository
     */
    protected $permissionRepository;

    public function __construct(PermissionRepository $permissionRepository) {
        parent::__construct();
        $this->permissionRepository = $permissionRepository;
    }
    
   /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $donnees = $this->permissionRepository->all();
        return new PermissionsResource($donnees);
 }
    //     public function index(Request $request)
    // {
    //     $subPermissionsByGroup = Permission::listSubPermissionsByGroup();

    //     return response()->json(['data' => $subPermissionsByGroup]);
    //     // Vous pouvez également utiliser votre ressource si nécessaire
    //     // return new PermissionsResource($subPermissionsByGroup);
    // }
   
    
}
