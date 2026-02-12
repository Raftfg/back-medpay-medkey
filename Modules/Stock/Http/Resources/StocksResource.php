<?php

namespace Modules\Stock\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class StocksResource extends ResourceCollection
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
            'data' => StockResource::collection($this->collection),
        ];
    }
}
