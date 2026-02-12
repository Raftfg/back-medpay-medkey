<?php

namespace Modules\Hospitalization\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class RoomsResource extends ResourceCollection
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
            'data' => RoomResource::collection($this->collection),
        ];
    }
}
