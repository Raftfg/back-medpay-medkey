<?php

namespace Modules\Absence\Http\Resources;

class TypeVacationResource extends \App\Http\Resources\BaseResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

