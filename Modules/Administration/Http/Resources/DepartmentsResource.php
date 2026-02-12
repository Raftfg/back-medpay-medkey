<?php

namespace Modules\Administration\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DepartmentsResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => DepartmentResource::collection($this->collection),
        ];
    }
}
