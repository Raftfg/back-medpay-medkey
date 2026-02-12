<?php

namespace Modules\Administration\Http\Resources;

// use Modules\Acl\Http\Resources\UserResource;
// use Modules\Absence\Http\Resources\MissionResource;
// use Modules\Absence\Http\Resources\VacationResource;

class InsuranceResource extends \App\Http\Resources\BaseResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // $acl = $this->displayAcl("Insurance");
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            // 'insuranceComp' => $this->insuranceComp,
            // 'number_insurance' => $this->number_insurance,
            // 'is_convention' => $this->is_convention,
            // 'phone' => $this->phone,
            'is_synced' => $this->is_synced,
            'deleted_at' => $this->deleted_at,
            // 'users' => new UserResource($this->user),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];
    }
}
