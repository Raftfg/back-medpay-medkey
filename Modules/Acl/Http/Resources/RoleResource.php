<?php

namespace Modules\Acl\Http\Resources;

use Modules\Acl\Http\Resources\PermissionResource;

class RoleResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        // $acl = $this->displayAcl("Role");
        // if(!$this->isDeleted()){
        //     $acl['delete'] = false;
        // }
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            // 'display_name' => $this->display_name,
            'permissions' => PermissionResource::collection($this->permissions),
            'permissions_uuid' => $this->permissions()->pluck('uuid')->toArray(),
            
            // 'acl' => $acl,
        ];
    }

}
