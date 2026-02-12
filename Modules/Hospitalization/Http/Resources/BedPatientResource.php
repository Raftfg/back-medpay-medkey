<?php

namespace Modules\Hospitalization\Http\Resources;

use Carbon\Carbon;
use Modules\Acl\Http\Resources\UserResource;
use Modules\Patient\Http\Resources\PatienteResource;

class BedPatientResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */
    public function toArray($request) 
    {
        // $acl = $this->displayAcl("Category");

        $startDate = $this->start_occupation_date ? Carbon::parse($this->start_occupation_date) : null;
        $endDate = $this->end_occupation_date ? Carbon::parse($this->end_occupation_date) : null;
        
        // Calculer la durée de séjour en jours
        $durationDays = null;
        if ($startDate && $endDate) {
            $durationDays = $startDate->diffInDays($endDate);
        } elseif ($startDate && !$endDate) {
            // Si pas de date de sortie, calculer depuis le début jusqu'à maintenant
            $durationDays = $startDate->diffInDays(Carbon::now());
        }

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'comment' => $this->comment,
            'number_of_days' => $this->number_of_days,
            'state' => $this->state,
            'start_occupation_date' => $startDate ? $startDate->format('d-m-Y H:i:s') : null,
            'end_occupation_date' => $endDate ? $endDate->format('d-m-Y H:i:s') : null,
            'start_occupation_date_raw' => $this->start_occupation_date,
            'end_occupation_date_raw' => $this->end_occupation_date,
            'duration_days' => $durationDays,
            'is_active' => !$endDate, // Actif si pas de date de sortie

            'bed' => $this->when($this->bed, function () {
                $bedResource = new BedResource($this->bed);
                // S'assurer que la relation room et service sont chargées
                if ($this->bed->relationLoaded('room')) {
                  $bedResource->resource->loadMissing('room.service');
                }
                return $bedResource;
            }),
            'patient' => $this->when($this->patient, function () {
                return new PatienteResource($this->patient);
            }),
            'movment' => $this->when($this->movment, function () {
                return [
                    'uuid' => $this->movment->uuid,
                    'ipp' => $this->movment->ipp,
                    'iep' => $this->movment->iep,
                    'admission_type' => $this->movment->admission_type,
                    'arrivaldate' => $this->movment->arrivaldate,
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
