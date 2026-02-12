<?php

namespace Modules\Annuaire\Http\Resources;

use Modules\Annuaire\Http\Resources\EmployerResource;

class ContratResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        // $acl = $this->displayAcl("Contrat");
        return [
            'uuid' => $this->uuid,
            'employment_type' => $this->employment_type,
            'salary' => $this->salary,
            'employment_start_date' => $this->employment_start_date,
            'employment_end_date' => $this->employment_end_date,
            'employer' => new EmployerResource($this->employee),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];
    }

}
