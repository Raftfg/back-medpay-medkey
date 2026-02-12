<?php

namespace Modules\Absence\Http\Resources;

class TypeVacationsResource extends \App\Http\Resources\BaseResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => TypeVacationResource::collection($this->collection),
        ];
    }
}

