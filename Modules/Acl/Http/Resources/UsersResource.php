<?php

namespace Modules\Acl\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UsersResource extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
//            colonnes_table_affichable() => [
//                "name" => __("Nom"),
//                "prenom" => __("Prénom"),
//                "email" => __("E-mail"),
//                "role_nom" => __("Rôle"),
//                "adresse_code_civic" => __("Adresse"),
//                "adresse_rue" => __("Rue"),
//                "adresse_apt" => __("Apt"),
//                "adresse_code_postal" => __("Code Postal"),
//                "tel" => __("Téléphone"),
//                "tel_mobile" => __("Mobile"),
//                "fax" => __("Fax"),
//            ],
            'data' => UserResource::collection($this->collection),
        ];
    }
}
