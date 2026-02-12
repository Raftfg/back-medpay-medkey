<?php

namespace Modules\Hospitalization\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BedsResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // Si c'est une pagination, retourner la structure paginée
        if ($this->resource instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return [
                'data' => BedResource::collection($this->collection),
                'current_page' => $this->resource->currentPage(),
                'per_page' => $this->resource->perPage(),
                'total' => $this->resource->total(),
                'last_page' => $this->resource->lastPage(),
                'from' => $this->resource->firstItem(),
                'to' => $this->resource->lastItem(),
            ];
        }
        
        // Sinon, retourner une structure simple
        return [
            'data' => BedResource::collection($this->collection),
        ];
    }
}
