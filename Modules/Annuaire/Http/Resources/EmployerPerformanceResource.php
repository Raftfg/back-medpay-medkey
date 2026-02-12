<?php

namespace Modules\Annuaire\Http\Resources;

use Modules\Administration\Http\Resources\ServiceResource;

class EmployerPerformanceResource extends \App\Http\Resources\BaseResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'period_type' => $this->period_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'overall_score' => $this->overall_score,
            'criteria_scores' => $this->criteria_scores,
            'comments' => $this->comments,

            'employer' => $this->whenLoaded('employer', function () {
                return new EmployerResource($this->employer);
            }),
            'service' => $this->whenLoaded('service', function () {
                return new ServiceResource($this->service);
            }),

            'evaluator_user_id' => $this->evaluator_user_id,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

