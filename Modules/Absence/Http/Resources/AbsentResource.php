<?php

namespace Modules\Absence\Http\Resources;

use Modules\Acl\Http\Resources\UserResource;
use Modules\Absence\Http\Resources\MissionResource;
use Modules\Absence\Http\Resources\VacationResource;

class AbsentResource extends \App\Http\Resources\BaseResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $acl = $this->displayAcl("Absent");
        return [
            'uuid' => $this->uuid,
            'type' => $this->type,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'vacations' => new VacationResource($this->vacation),
            'missions' => new MissionResource($this->mission),
            'users' => new UserResource($this->user),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'acl' => $acl,
        ];
    }
}
