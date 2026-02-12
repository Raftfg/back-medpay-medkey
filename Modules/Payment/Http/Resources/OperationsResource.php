<?php

namespace Modules\Payment\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OperationsResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) // toArray est une method definit par laravel
    {
        return [
            'data' => OperationResource::collection($this->collection),
        ];
    }
}
