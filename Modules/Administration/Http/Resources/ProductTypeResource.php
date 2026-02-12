<?php

namespace Modules\Administration\Http\Resources;

use Modules\Acl\Http\Resources\UserResource;
// use Modules\Absence\Http\Resources\MissionResource;
// use Modules\Absence\Http\Resources\VacationResource;

class ProductTypeResource extends \App\Http\Resources\BaseResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $acl = $this->displayAcl("ProductType");
        return [
            'uuid' => $this->uuid,
            'designation' => $this->designation,
            'description' => $this->description,
            'is_synced' => $this->is_synced,
            'deleted_at' => $this->deleted_at,
            'users' => new UserResource($this->user),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'acl' => $acl,
        ];
    }
}
