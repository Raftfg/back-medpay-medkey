<?php

namespace Modules\Movment\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MovmentsResource extends ResourceCollection
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
            'data' => MovmentResource::collection($this->collection),
        ];
    }
}
