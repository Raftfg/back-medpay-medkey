<?php

namespace Modules\Stock\Http\Resources;

use Carbon\Carbon;
use Modules\Acl\Http\Resources\UserResource;

class StockTransferResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */
    public function toArray($request) {
        // $acl = $this->displayAcl("StockTransfer");
        $productsNumber = count($this->stockTransferProducts->pluck('product_id')->unique());

        return [
            'uuid' => $this->uuid,
            'comment' => $this->comment,
            'model_name' => $this->model_name,
            'model_id' => $this->model_id,
            'stock_source' => new StockResource($this->fromStock),
            'stock_destination' => new StockResource($this->toStock),
            'products_number' => $productsNumber,


            'user' => new UserResource($this->user),
            'is_synced' => $this->is_synced,
            'deleted_at' => $this->deleted_at,

            'created_at' => Carbon::parse($this->created_at)->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];
    }
}
