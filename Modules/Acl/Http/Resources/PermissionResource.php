<?php

namespace Modules\Acl\Http\Resources;

class PermissionResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'display_name' => $this->display_name,
            'groupe' => $this->groupe,
            //'guard_name' => $this->guard_name,
            /*'links' => [
                'self' => null,
            ],*/
        ];
    }

}
