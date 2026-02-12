<?php

namespace Modules\Stock\Http\Resources;

use Modules\Acl\Http\Resources\UserResource;

class SupplyProductResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */
    public function toArray($request) {
        // $acl = $this->displayAcl("SupplyProduct");

        return [

            'uuid' => $this->uuid,
            'lot_number' => $this->lot_number,
            'units_per_box' => $this->units_per_box,
            'expire_date' => $this->expire_date,
            'quantity' => $this->quantity,
            'purchase_price' => $this->purchase_price,
            'profit_margin' => $this->profit_margin,

            'supply' =>  new SupplyResource($this->supply),
            'product' => new ProductResource($this->product),
            'supplier' => new ProductResource($this->supplier),

            'is_synced' => $this->is_synced,
            'deleted_at' => $this->deleted_at,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];
    }

}
