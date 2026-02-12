<?php

namespace Modules\Annuaire\Http\Resources;

class CompetenceResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        $acl = $this->displayAcl("Competence");
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            // 'users' => new UserResource($this->users), 

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            'acl' => $acl,
        ];
    }

}
