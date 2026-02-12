<?php

namespace Modules\Absence\Http\Resources;

use Modules\Acl\Http\Resources\UserResource;
use Modules\Absence\Http\Resources\TypeVacationResource;

class VacationResource extends \App\Http\Resources\BaseResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'note' => $this->note,
            'motif_urgence' => $this->motif_urgence,
            'departmentss_id' => $this->departmentss_id,
            'reject_reason' => $this->reject_reason,
            'decision_chief' => $this->decision_chief,
            'pathFile' => $this->pathFile,
            'users_id' => $this->users_id,
            'type_vacations_id' => $this->type_vacations_id,

            'user' => $this->whenLoaded('user', function () {
                return new UserResource($this->user);
            }),
            'type_vacation' => $this->whenLoaded('typeVacation', function () {
                return new TypeVacationResource($this->typeVacation);
            }),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

