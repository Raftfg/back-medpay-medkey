<?php

namespace Modules\Stock\Http\Resources;

use Carbon\Carbon;
use Modules\Acl\Http\Resources\UserResource;

class SupplyResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */
    public function toArray($request) {
        // $acl = $this->displayAcl("Supply");

        return [
            'uuid' => $this->uuid,
            'numero' => $this->numero,
            'total' => $this->total,
            
            'stock' =>  new StockResource($this->stock),

            'user' => new UserResource($this->user),
            'is_synced' => $this->is_synced,
            'deleted_at' => $this->deleted_at,

            'created_at' => Carbon::parse($this->created_at)->format('d-m-Y H:i:s'),
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];
    }

}
