<?php

namespace Modules\Annuaire\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class FormationsResource extends ResourceCollection
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
            'data' => FormationResource::collection($this->collection),
        ];
    }
}
