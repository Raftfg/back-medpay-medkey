<?php

namespace Modules\Annuaire\Http\Resources;

use Modules\Administration\Http\Resources\DepartmentResource;
use Modules\Administration\Http\Resources\ServiceResource;

class EmployerResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        // $acl = $this->displayAcl("Employer");
        return [
            'uuid' => $this->uuid,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'address' => $this->address,
            'hire_date' => $this->hire_date,
            'termination_date' => $this->termination_date,
            'date_birth' => $this->date_birth,
            'employment_status' => $this->employment_status,
            'social_security_number' => $this->social_security_number,
            'sex' => $this->sex,
            'birthplace' => $this->birthplace,
            'marital_status' => $this->marital_status,
            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'charge' => $this->charge,
            'urgency_phone' => $this->urgency_phone,
            'urgency_name' => $this->urgency_name,
            'nationality' => $this->nationality,
            'contract_lenght' => $this->contract_lenght,
            'work_time' => $this->work_time,
            'contract_type' => $this->contract_type,
            'salary' => $this->salary,
            'function' => $this->function,
            'ifu' => $this->ifu,
            'npi' => $this->npi,
            'motif' => $this->motif,

            'departments' => new DepartmentResource($this->department),
            'services' => new ServiceResource($this->service),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];


    }

}
