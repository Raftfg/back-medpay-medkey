<?php

namespace Modules\Annuaire\Http\Resources;

class FormationResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        $acl = $this->displayAcl("Formation");
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'place' => $this->place,
            'document_link' => $this->document_link,
            // 'users' => new UserResource($this->users), 

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            'acl' => $acl,
        ];
    }

}
