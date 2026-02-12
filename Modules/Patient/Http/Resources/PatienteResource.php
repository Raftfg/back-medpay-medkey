<?php

namespace Modules\Patient\Http\Resources;

use Modules\Acl\Http\Resources\UserResource;
use Modules\Administration\Http\Resources\PaysResource;
use Modules\Administration\Http\Resources\CommuneResource;
use Modules\Patient\Http\Resources\PatientInsuranceResource;
use Modules\Administration\Http\Resources\DepartementResource;
use Modules\Administration\Http\Resources\ArrondissementResource;

class PatienteResource extends \App\Http\Resources\BaseResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // $acl = $this->displayAcl("Patiente");
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'ipp' => $this->ipp,
            'lastname' => $this->lastname,
            'is_synced' => $this->is_synced,
            'deleted_at' => $this->deleted_at,
            'firstname' => $this->firstname,
            'date_birth' => $this->date_birth,
            'age' => $this->age,
            'maison' => $this->maison,
            'phone' => $this->phone,
            'email' => $this->email,
            'whatsapp' => $this->whatsapp,
            'profession' => $this->profession,
            'gender' => $this->gender,
            'emergency_contac' => $this->emergency_contac,
            'marital_status' => $this->marital_status,
            'autre' => $this->autre,
            'nom_marital' => $this->nom_marital,
            'date_deces' => $this->date_deces,
            'code_postal' => $this->code_postal,
            'nom_pere' => $this->nom_pere,
            'phone_pere' => $this->phone_pere,
            'nom_mere' => $this->nom_mere,
            'phone_mere' => $this->phone_mere,
            'quartier' => $this->quartier,
            'pays' => $this->pays ? new PaysResource($this->pays) : null,
            'departements' => $this->departement ? new DepartementResource($this->departement) : null,
            'communes' => $this->commune ? new CommuneResource($this->commune) : null,
            'arrondissements' => $this->arrondissement ? new ArrondissementResource($this->arrondissement) : null,
            'patient_insurances' => $this->patientInsurances ? new PatientInsurancesResource($this->patientInsurances) : null,
            // 'user' => new UserResource($this->user),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];
    }
}