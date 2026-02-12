<?php

namespace Modules\Administration\Http\Resources;

use Modules\Acl\Http\Resources\UserResource;
use Modules\Administration\Http\Resources\InsuranceResource;
// use Modules\Administration\Http\Resources\ProductTypeResource;

class PackResource extends \App\Http\Resources\BaseResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // $acl = $this->displayAcl("Pack");
        return [
            'uuid' => $this->uuid,
            'designation' => $this->designation,
            'percentage' => $this->percentage,
            'is_synced' => $this->is_synced,
            'deleted_at' => $this->deleted_at,
            'insurances' => new InsuranceResource($this->insurance),
            // 'product_types' => new ProductTypeResource($this->product_type),
            'users' => new UserResource($this->user),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // 'acl' => $acl,
        ];
    }
}
