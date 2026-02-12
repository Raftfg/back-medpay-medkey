<?php

namespace Modules\Annuaire\Http\Resources;

class Experience_proResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        $acl = $this->displayAcl("Experience_pro");
        return [
            'uuid' => $this->uuid,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'place' => $this->place,
            'job' => $this->job,
            // 'users' => new UserResource($this->users), 
            'missions' => $this->missions,
            'document_link' => $this->document_link,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            'acl' => $acl,
        ];
    }

}
