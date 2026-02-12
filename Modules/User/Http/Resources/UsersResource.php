<?php

namespace Modules\User\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UsersResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) // toArray est une method definit par laravel
    {
        return [
            'data' => UserResource::collection($this->collection),
        ];
    }
}
