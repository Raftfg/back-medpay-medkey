<?php

namespace Modules\Planning\Http\Resources;

use Modules\Annuaire\Http\Resources\EmployerResource;
use Modules\Administration\Http\Resources\ServiceResource;

class WorkScheduleResource extends \App\Http\Resources\BaseResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'employer_id' => $this->employer_id,
            'service_id' => $this->service_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'period_type' => $this->period_type,
            'status' => $this->status,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
            'created_by' => $this->created_by,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            
            'employer' => $this->whenLoaded('employer', function () {
                return new EmployerResource($this->employer);
            }),
            'service' => $this->whenLoaded('service', function () {
                return new ServiceResource($this->service);
            }),
            'shifts' => $this->whenLoaded('shifts', function () {
                return ScheduleShiftResource::collection($this->shifts);
            }),
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
