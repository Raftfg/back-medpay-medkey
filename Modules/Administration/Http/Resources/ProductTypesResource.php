<?php

namespace Modules\Administration\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductTypesResource extends ResourceCollection
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
            'data' => ProductTypeResource::collection($this->collection),
        ];
    }
}
