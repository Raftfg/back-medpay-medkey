<?php

namespace Modules\Hospitalization\Http\Resources;

use Modules\Acl\Http\Resources\UserResource;

class RoomResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */
    public function toArray($request) {
        // $acl = $this->displayAcl("Room");
        // Vérifier si la relation beds existe et n'est pas null avant d'appeler count()
        $bedCount = 0;
        if ($this->beds !== null) {
            $bedCount = $this->beds->count();
        }

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'bed_capacity' => $this->bed_capacity,
            'price' => $this->price,
            'description' => $this->description,
            'bed_count' => $bedCount,
            'services_id' => $this->services_id,
            'service' => $this->when($this->service, function () {
                return [
                    'id' => $this->service->id,
                    'name' => $this->service->name,
                ];
            }),
            
            // 'user' => new UserResource($this->user),

            'is_synced' => $this->is_synced,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];
    }
}
