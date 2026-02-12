<?php

namespace Modules\Annuaire\Http\Resources;

class EmployerPerformancesResource extends \App\Http\Resources\BaseResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => EmployerPerformanceResource::collection($this->collection),
        ];
    }
}

