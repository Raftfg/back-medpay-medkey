<?php

namespace Modules\Stock\Http\Resources;

use Modules\Acl\Http\Resources\UserResource;

class SupplierResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */

    public function toArray($request) {
        // $acl = $this->displayAcl("SupplierResource");

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'dial_code' => $this->dial_code,
            'phone_number' => $this->phone_number,
            'address' => $this->address,
            'profit_margin' => $this->profit_margin,

            'user' => new UserResource($this->user),
            'is_synced' => $this->is_synced,
            'deleted_at' => $this->deleted_at,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];
    }

}
