<?php

namespace Modules\Stock\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SuppliersResource extends ResourceCollection
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
            'data' => SupplierResource::collection($this->collection),
        ];
    }
}
