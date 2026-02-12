<?php

namespace Modules\Stock\Http\Resources;

use Modules\Acl\Http\Resources\UserResource;

class DestockResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */
    public function toArray($request) {
        // $acl = $this->displayAcl("Stock");
        $remainingToRetrieve = max(0, $this->quantity_ordered - $this->quantity_retrieved);

        return [
            'uuid' => $this->uuid,
            'reference_facture' => $this->reference_facture,
            'quantity_retrieved' => $this->quantity_retrieved,
            'quantity_ordered' => $this->quantity_ordered,
            'quantity_remaining_to_retrieve' => $remainingToRetrieve,
            'stock_product' => new StockProductResource($this->stockProduct),

            'user' => new UserResource($this->user),
            'is_synced' => $this->is_synced,
            'deleted_at' => $this->deleted_at,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];
    }

}
