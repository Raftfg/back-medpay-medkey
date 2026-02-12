<?php

namespace Modules\Notifier\Http\Resources;


class NotifierTrackingResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        return [
            'uuid' => $this->uuid,
            'sujet' => $this->sujet,
            'message' => $this->message,
            'destinataires' => $this->destinataires,
            'objet' => $this->objet,
            'nombre_fois' => $this->nombre_fois,
        ];
    }

}
