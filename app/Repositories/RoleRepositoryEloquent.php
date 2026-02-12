<?php

namespace App\Repositories;

use App\Repositories\AppBaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Contracts\RepositoryInterface;
use Modules\Acl\Entities\Role;
use Modules\Acl\Entities\Permission;

/**
 * Class TenantRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class RoleRepositoryEloquent extends AppBaseRepository implements RepositoryInterface {

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model() {
        return Role::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot() {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * Créer le rôle et avec éventuellement les permissions
     * @param type Role
     */
    public function creerRole($attributs) {
        $display_name = $attributs['display_name'];

        $role = $this->create([
            'display_name' => $display_name,
            'guard_name' => guard_web(),    //forcer le guard parce que ça balance entre api et web selon le contexte d'exécution
        ]);
        $role = $role->fresh();

        if (isset($attributs['permissions'])) {
            $permissions = Permission::whereIn('uuid', $attributs['permissions'])->where(['guard_name' => guard_web()])->get();
            $role->syncPermissions($permissions);   //Détache d'abord les permissions puis attache après
        }
        
        return $role;
    }

    /**
     * Mettre à jour le rôle et avec éventuellement les permissions
     * @param type Role
     */
    public function majRole($uuid, $attributs) {
        $role = $this->findByUuid($uuid)->first();
        
        $display_name = $attributs['display_name'];

        $role = $this->update([
            'display_name' => $display_name,
        ], $role->id);
        $role = $role->fresh();

        if (isset($attributs['permissions'])) {
            $permissions = Permission::whereIn('uuid', $attributs['permissions'])->where(['guard_name' => guard_web()])->get();
            $role->syncPermissions($permissions);   //Détache d'abord les permissions puis attache après
        }
        
        return $role;
    }

}
