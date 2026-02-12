<?php

namespace Modules\Acl\Http\Resources;

use Modules\Acl\Http\Resources\RoleResource;

class UserResource extends \App\Http\Resources\BaseResource
{

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // $acl = $this->displayAcl("User");
        $moduleAlias = strtolower(config('acl.name'));
        $media_collection_name = config("$moduleAlias.media_collection_name");

        // if ($this->isMagasinier()) {
        //     $acl['delete'] = false;
        // }
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'prenom' => $this->prenom,
            'telephone' => $this->telephone,
            'adresse' => $this->adresse,
            'sexe' => $this->sexe,
            // 'full_name' => $this->full_name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            //            'tel' => $this->tel,
            // 'tel_mobile' => $this->tel_mobile,
            //            'fax' => $this->fax,
            'roles' => RoleResource::collection($this->roles),
            // 'role_nom' => implode(', ', $this->roles()->pluck('display_name')->toArray()),
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            // 'medias' => $this->obtenirMediaUrlsFormates($media_collection_name),

            // 'acl' => $acl,
        ];
    }
}
