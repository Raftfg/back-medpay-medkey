<?php

namespace Modules\Stock\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TypeProductsResource extends ResourceCollection
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
            'data' => TypeProductResource::collection($this->collection)
        ];
    }
}
