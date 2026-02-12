<?php

namespace Modules\Stock\Http\Resources;

use Carbon\Carbon;
use Modules\Acl\Http\Resources\UserResource;

class StockTransferProductResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */
    public function toArray($request) {
        // $acl = $this->displayAcl("StockTransferProduct");

        return [
            'uuid' => $this->uuid,
            'quantity_transfered' => $this->quantity_transfered,
            'stock_product' => new StockProductResource($this->stockProduct),
            'stock_transfer' => new StockTransferResource($this->stockTransfer),

            'is_synced' => $this->is_synced,
            'deleted_at' => $this->deleted_at,

            'created_at' => Carbon::parse($this->created_at)->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];
    }

}
