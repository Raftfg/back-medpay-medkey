<?php

namespace Modules\Stock\Http\Resources;

use Modules\Acl\Http\Resources\UserResource;

class StockResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */
    public function toArray($request) {
        // $acl = $this->displayAcl("Stock");

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'store' => new StoreResource($this->store),
            'for_pharmacy_sale' => $this->for_pharmacy_sale,

            // 'user' => new UserResource($this->user),
            'is_synced' => $this->is_synced,
            'deleted_at' => $this->deleted_at,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];
    }

}
