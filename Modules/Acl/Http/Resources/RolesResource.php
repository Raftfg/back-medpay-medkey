<?php

namespace Modules\Acl\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RolesResource extends ResourceCollection
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
            // colonnes_table_affichable() => [
            //     'display_name' => __("Nom"),
            // ],
            'data' => RoleResource::collection($this->collection),
        ];
    }
}
