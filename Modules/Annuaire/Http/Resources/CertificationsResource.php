<?php

namespace Modules\Annuaire\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CertificationsResource extends ResourceCollection
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
            'data' => CertificationResource::collection($this->collection),
        ];
    }
}
