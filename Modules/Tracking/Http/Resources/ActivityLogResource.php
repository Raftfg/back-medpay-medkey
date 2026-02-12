<?php

namespace Modules\Tracking\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Acl\Entities\User;

class ActivityLogResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        $current_user = user_api();
        $acl = [
            'create' => false,
            'read' =>   $current_user ? $current_user->isPermission("read ActivityLog") : false,
            'update' => false,
            'delete' => false,
        ];

        return [
            'uuid' => $this->uuid,
            'log_name' => $this->log_name,
            'description' => $this->description,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'event' => $this->event,
            'causer_type' => $this->causer_type,
            'causer_id' => $this->causer_id,
            'causer' => $this->causer_id ? $this->getCauser($this->causer_id) : null,
            'properties' => $this->properties,
            'batch_uuid' => $this->batch_uuid,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'created_at_date' => $this->created_at->format('Y-m-d'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'chaine_valeur_code' => $this->chaine_valeur_code,
            
            'acl' => $acl,
        ];
    }

    /**
     * Retourne l'auteur du log
     * @param type $userId
     * 
     * @return User
     */
    private function getCauser($userId){
        return User::where('id', $userId)->select('uuid', 'name', 'prenom')->first();
    }
}
