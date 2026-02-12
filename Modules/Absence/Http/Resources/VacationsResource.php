<?php

namespace Modules\Absence\Http\Resources;

class VacationsResource extends \App\Http\Resources\BaseResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => VacationResource::collection($this->collection),
        ];
    }
}

