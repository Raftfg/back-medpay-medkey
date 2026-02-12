<?php

namespace Modules\Acl\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Response;
// use Modules\Acl\Entities\Role;
use Illuminate\Support\Facades\DB;
// use Modules\Acl\Entities\Permission;
use Modules\Acl\Http\Resources\RoleResource;
use Modules\Acl\Repositories\RoleRepository;
use Modules\Acl\Http\Resources\RolesResource;
use App\Http\Controllers\Api\V1\ApiController;
use Modules\Acl\Http\Requests\RoleIndexRequest;
use Modules\Acl\Http\Requests\RoleStoreRequest;
use Modules\Acl\Http\Requests\RoleDeleteRequest;
use Modules\Acl\Http\Requests\RoleUpdateRequest;

class RoleController extends \Modules\Acl\Http\Controllers\AclController {

    /**
     * @var PostRepository
     */
    protected $roleRepositoryEloquent;

    public function __construct(RoleRepository $roleRepositoryEloquent) {
        parent::__construct();
        $this->roleRepositoryEloquent = $roleRepositoryEloquent;
    }
    
   /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(RoleIndexRequest $request)
    {
        $donnees = $this->roleRepositoryEloquent->orderBy('created_at', 'desc')->paginate($this->nombrePage);
        return new RolesResource($donnees);
    }    
     /**
     * Show a resource.
     * 
     * @return Response
     */
    public function show(RoleIndexRequest $request, $uuid)
    {
        $item = $this->roleRepositoryEloquent->findByUuidOrFail($uuid)->first(); 
        return new RoleResource($item);
    }

   /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(RoleStoreRequest $request)
    {
        $item = $this->roleRepositoryEloquent->creerRole($request->all());
        $item = $item->fresh();
        return new RoleResource($item);
    }

    // public function droitUsers(Request $request)
    // {
    //     // dd("data", $request);
    //     $role = Role::find($request->uuid);

    //     foreach ($request->permissions as $permission) {
    //         $permissions = Permission::find($permission);
    //         $role->givePermissionTo($permissions);
    //         // $permission->assignRole($role);
    //     }
    //     // // Réponse JSON avec les données du rôle
    //     return response()->json([
    //         'success' => true,
    //         'data' => $role
    //     ]);
    // }

            public function droitUsers(Request $request)
        {
            try {
                // Recherche du rôle par UUID
                $role = Role::where('uuid', $request->uuid)->first();

                if (!$role) {
                    return response()->json(['message' => 'Rôle non trouvé.'], 404);
                }

                // Attribution des permissions au rôle
                foreach ($request->permissions as $permissionUuid) {
                    $permission = Permission::where('uuid', $permissionUuid)->first();

                    if ($permission) {
                        $role->givePermissionTo($permission);
                    }
                }

                // Réponse JSON avec les données du rôle
                return response()->json([
                    'success' => true,
                    'data' => $role
                ]);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Une erreur s\'est produite.'], 500);
            }
        }

        public function getPermissionsForRole($roleUuid)
        {
            try {
                $role = Role::where('uuid', $roleUuid)->first();
                
                $permissions = $role->permissions;
        
                return response()->json($permissions, 200);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Une erreur s\'est produite.', 'error' => $e->getMessage()], 500);
            }
        }
         
        //  grouper par module
        // public function getPermissionsForRole($roleUuid)
        // {
        //     try {
        //         // Récupérer le rôle à partir de l'UUID
        //         $role = Role::where('uuid', $roleUuid)->first();
        
        //         // Vérifier si le rôle existe
        //         if ($role) {
        //             // Récupérer les permissions associées au rôle
        //             $permissions = $role->permissions;
        
        //             // Grouper les permissions par groupe (ou module)
        //             $groupedPermissions = $permissions->groupBy('groupe');
        
        //             // Convertir le résultat en tableau
        //             $groupedPermissionsArray = $groupedPermissions->toArray();
        
        //             return response()->json($groupedPermissionsArray, 200);
        //         } else {
        //             return response()->json(['message' => 'Le rôle spécifié n\'a pas été trouvé.'], 404);
        //         }
        //     } catch (\Exception $e) {
        //         return response()->json(['message' => 'Une erreur s\'est produite.', 'error' => $e->getMessage()], 500);
        //     }
        // }


            public function detachPermissionsFromRole(Request $request, $roleUuid)
        {
            try {
                $role = Role::where('uuid', $roleUuid)->first();

                if (!$role) {
                    return response()->json(['message' => 'Le rôle spécifié n\'existe pas.'], 404);
                }

                $permissionsToDetach = $request->input('permissions', []);

                if (empty($permissionsToDetach)) {
                    return response()->json(['message' => 'Aucune permission spécifiée pour détachement.'], 400);
                }

                $detachedPermissions = [];

                foreach ($permissionsToDetach as $permissionUuid) {
                    $permission = Permission::where('uuid', $permissionUuid)->first();

                    if ($permission) {
                        $role->revokePermissionTo($permission);
                        $detachedPermissions[] = $permission->name;
                    }
                }

                return response()->json([
                    'message' => 'Les permissions ont été détachées avec succès.',
                    'detached_permissions' => $detachedPermissions,
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Une erreur s\'est produite.',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    
    
   /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(Request $request, $uuid)
    {
        $item = $this->roleRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        // $item = $this->roleRepositoryEloquent->majRole($uuid, $request->all());
        
        $item = $this->roleRepositoryEloquent->update($request->all(), $item->id);
        $item = $item->fresh();
        return new RoleResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(RoleDeleteRequest $request, $uuid)
    {
        $role = $this->roleRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
            $this->roleRepositoryEloquent->delete($role->id);
        
        // $data = [
        //     // "message" => $this->messageSuppressionPossibleOui,
        //     "message" => __("Utilisateur supprimé avec succès"),
        // ];
        $data = [
            "message" => __("Rôle supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }
    
   /**
     * Lister les rôles internes.
     *
     * @return Response
     */
    public function roleInternes(RoleIndexRequest $request)
    {
        $donnees = $this->roleRepositoryEloquent->interne()->whereNonSuperAdmin()->get();
        return new RolesResource($donnees);
    }
    
   /**
     * Lister les rôles externes.
     *
     * @return Response
     */
    public function roleExternes(RoleIndexRequest $request)
    {
        $donnees = $this->roleRepositoryEloquent->externe()->get();
        return new RolesResource($donnees);
    }
}
