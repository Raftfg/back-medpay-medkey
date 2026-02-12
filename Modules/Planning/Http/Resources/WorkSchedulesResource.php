<?php

namespace Modules\Planning\Http\Resources;

class WorkSchedulesResource extends \App\Http\Resources\BaseResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => WorkScheduleResource::collection($this->collection),
        ];
    }
}
