<?php

namespace Modules\User\Http\Resources;

// use Modules\Acl\Http\Resources\MovmentResource; 
use Modules\Movment\Http\Resources\MovmentResource; 
use Modules\Absence\Http\Resources\Resource;

class UserResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        // $acl = $this->displayAcl("User");
        return [
            // 'uuid' => $this->uuid,
            // 'start_date' => $this->start_date,
            // 'end_date' => $this->end_date,
            // 'status' => $this->status,
            // 'note' => $this->note,
            // 'motif_urgence' => $this->motif_urgence,
            // // 'departments' => new DepartmentResource($this->departments), // Ici on met les noms sans "id",
            // 'reject_reason' => $this->reject_reason,
            // 'decision_chief' => $this->decision_chief,
            // 'pathFile' => $this->pathFile,
            // 'users' => new UserResource($this->user), // Ici on met les noms sans "id et ici on crée une instance du model associé et en Json"
            // 'type_Users' => new TypeUserResource($this->type_User),

            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            
            // 'acl' => $acl,
        ];
    }

}
