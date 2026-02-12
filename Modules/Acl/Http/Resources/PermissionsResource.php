<?php

namespace Modules\Acl\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Modules\Acl\Entities\Permission;

class PermissionsResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            colonnes_table_affichable() => [
                'display_name' => __("Nom"),
            ],
            'data' => PermissionResource::collection($this->collection),
            'permission_groupes' => Permission::select('groupe')->distinct()->get(),
            /*'links' => [
                'self' => null,
            ],*/
        ];
    }
}
