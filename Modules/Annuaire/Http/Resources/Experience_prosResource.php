<?php

namespace Modules\Annuaire\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class Experience_prosResource extends ResourceCollection
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
            'data' => Experience_proResource::collection($this->collection),
        ];
    }
}
