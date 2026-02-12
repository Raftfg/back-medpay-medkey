<?php

namespace Modules\Hospitalization\Http\Resources;

use Modules\Acl\Http\Resources\UserResource;
use Modules\Patient\Http\Resources\PatienteResource;

class BedResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */
    public function toArray($request) {
        // $acl = $this->displayAcl("Category");
        // $displayState = ($this->state === 'free') ? 'Libre' : 'Occupé';

        // Récupérer le patient actuel via currentStay si disponible
        $currentPatient = null;
        if ($this->currentStay && $this->currentStay->patient) {
            $currentPatient = $this->currentStay->patient;
        } elseif ($this->patient) {
            $currentPatient = $this->patient;
        }

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'state' => $this->state,
            'room_id' => $this->room_id,

            'room' => $this->when($this->room, function () {
                return new RoomResource($this->room);
            }),
            'patient' => $this->when($currentPatient, function () use ($currentPatient) {
                return new PatienteResource($currentPatient);
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
