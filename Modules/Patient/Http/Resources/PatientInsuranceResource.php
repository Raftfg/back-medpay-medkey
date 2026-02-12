<?php

namespace Modules\Patient\Http\Resources;

use Modules\Acl\Http\Resources\UserResource;
use Modules\Patient\Http\Resources\PatienteResource;
use Modules\Administration\Http\Resources\PackResource;


class PatientInsuranceResource extends \App\Http\Resources\BaseResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // $acl = $this->displayAcl("PatientInsurance");
        return [
         
            'uuid' => $this->uuid,
            'date_debut' => $this->date_debut,
            'date_fin' => $this->date_fin,
            // 'observation' => $this->date_fin,
            'numero_police' => $this->numero_police,
            'pack' => new PackResource($this->pack),
            // 'patient' => new PatienteResource($this->patient), // Ici on met les noms sans "id"
            // 'type_patientInsurances' => new TypePatientInsuranceResource($this->type_patientInsurance),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];
    }
}