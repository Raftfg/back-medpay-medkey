<?php

namespace Modules\Administration\Http\Resources;

use Modules\Administration\Http\Resources\DepartementResource;

// use Modules\Acl\Http\Resources\UserResource;
// use Modules\Absence\Http\Resources\MissionResource;
// use Modules\Absence\Http\Resources\VacationResource;

class CommuneResource extends \App\Http\Resources\BaseResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->resource === null) {
            return [
                'id' => null,
                'nom' => null,
                'departements' => null,
            ];
        }

        return [
            "id" => $this->id,
            'nom' => $this->nom,
            'departements' => $this->departement ? new DepartementResource($this->departement) : null,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // 'acl' => :$acl,
        ];
    }
}
