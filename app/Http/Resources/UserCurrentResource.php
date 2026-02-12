<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Acl\Http\Resources\RoleResource;
use Modules\Acl\Http\Resources\PermissionResource;
use Modules\Acl\Entities\Permission;

class UserCurrentResource extends JsonResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'prenom' => $this->prenom,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'tel_mobile' => $this->tel_mobile,
            'email_verified_at' => $this->email_verified_at,
            'tel_mobile_verified_at' => $this->tel_mobile_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'roles' => RoleResource::collection($this->roles),
            'permissions_cles' => $this->setClePermissions(),
        ];
    }
    
    private function getPermissionNoms() {
        $nomPermissions = [];
        foreach($this->roles as $role){
            $nomPermissions += $role->permissions()->pluck('name')->toArray();
        }
        return $nomPermissions;
    }
    
    private function setClePermissions() {
        $nomPermissions = $this->getPermissionNoms();
        $permissions_cles = [];
        
        $permissionAll = Permission::select('name')->get();
        foreach($permissionAll as $permission){
            $permissions_cles[$permission->name] = in_array($permission->name, $nomPermissions);
        }
        return $permissions_cles;
    }

}
