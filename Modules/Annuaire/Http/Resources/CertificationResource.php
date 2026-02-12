<?php

namespace Modules\Annuaire\Http\Resources;

class CertificationResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        $acl = $this->displayAcl("Certification");
        return [
            'uuid' => $this->uuid,
            'description' => $this->description,
            'date' => $this->date,
            'garant' => $this->garant,
            'document_link' => $this->document_link,
            // 'users' => new UserResource($this->users), 

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            'acl' => $acl,
        ];
    }

}
