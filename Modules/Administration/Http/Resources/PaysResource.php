<?php

namespace Modules\Administration\Http\Resources;

// use Modules\Acl\Http\Resources\UserResource;
// use Modules\Absence\Http\Resources\MissionResource;
// use Modules\Absence\Http\Resources\VacationResource;

class PaysResource extends \App\Http\Resources\BaseResource
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
            ];
        }
        
        return [
            "id" => $this->id,
            'nom' => $this->nom,


            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // 'acl' => :$acl,
        ];
    }
}
