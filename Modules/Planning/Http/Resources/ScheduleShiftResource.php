<?php

namespace Modules\Planning\Http\Resources;

use Modules\Annuaire\Http\Resources\EmployerResource;
use Modules\Administration\Http\Resources\ServiceResource;

class ScheduleShiftResource extends \App\Http\Resources\BaseResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'work_schedule_id' => $this->work_schedule_id,
            'employer_id' => $this->employer_id,
            'shift_date' => $this->shift_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'shift_type' => $this->shift_type,
            'rotation_type' => $this->rotation_type,
            'service_id' => $this->service_id,
            'position' => $this->position,
            'status' => $this->status,
            'is_swap' => $this->is_swap,
            'swapped_with_id' => $this->swapped_with_id,
            'duration_hours' => $this->duration_hours ?? $this->calculateDuration(),
            'respects_legal_duration' => $this->respects_legal_duration,
            'respects_rest_period' => $this->respects_rest_period,
            'notes' => $this->notes,
            
            'employer' => $this->whenLoaded('employer', function () {
                return new EmployerResource($this->employer);
            }),
            'service' => $this->whenLoaded('service', function () {
                return new ServiceResource($this->service);
            }),
            'swapped_with' => $this->whenLoaded('swappedWith', function () {
                return new EmployerResource($this->swappedWith);
            }),
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
