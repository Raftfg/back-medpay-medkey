<?php

namespace Modules\Stock\Http\Resources;

use Modules\Acl\Http\Resources\UserResource;

class StockProductResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */
    public function toArray($request) {
        // $acl = $this->displayAcl("StockProduct");

        return [
            'uuid' => $this->uuid,
            'lot_number' => $this->lot_number,
            'units_per_box' => $this->units_per_box,
            'expire_date' => $this->expire_date,
            'quantity' => $this->quantity,
            'bulk_units' => $this->bulk_units,
            'purchase_price' => $this->purchase_price,
            'selling_price' => $this->selling_price,

            'stock' =>  new StockResource($this->stock),
            'product' => new ProductResource($this->product),

            // 'user' => new UserResource($this->user),
            'is_synced' => $this->is_synced,
            'deleted_at' => $this->deleted_at,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];
    }

}
