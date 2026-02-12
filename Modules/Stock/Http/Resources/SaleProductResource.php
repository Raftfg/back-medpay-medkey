<?php

namespace Modules\Stock\Http\Resources;

use Modules\Acl\Http\Resources\UserResource;

class SaleProductResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */
    public function toArray($request) {
        // $acl = $this->displayAcl("SaleProduct");

        return [
            'uuid' => $this->uuid,
            'price' => $this->price,
            'quantity' => $this->quantity,

            'sale' => new SaleResource($this->movement),
            'stock_products' => new StockProductResource($this->stockProduct),

            // 'user' => new UserResource($this->user),
            'is_synced' => $this->is_synced,
            'deleted_at' => $this->deleted_at,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];
    }

}
