<?php

namespace Modules\Notifier\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class NotifierTrackingsResource extends ResourceCollection {

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        return [
            colonnes_table_affichable() => [
                'sujet' => __("Sujet"),
                'message' => __("Message"),
                'destinataires' => __("Destinataires"),
                'nombre_fois' => __("Nombre d'envoi"),
            ],
            'data' => NotifierTrackingResource::collection($this->collection),
        ];
    }

}
