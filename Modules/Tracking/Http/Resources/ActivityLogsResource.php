<?php

namespace Modules\Tracking\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ActivityLogsResource extends ResourceCollection
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
            colonnes_table_affichable() => [
                "log_name" => __("Nom du log"),
                "description" => __("Description"),
                "created_at" => __("Date de log"),
            ],
            'data' => ActivityLogResource::collection($this->collection),
        ];
    }
}
