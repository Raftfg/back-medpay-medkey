<?php

namespace Modules\Stock\Http\Resources;

use Modules\Acl\Http\Resources\UserResource;
use Illuminate\Support\Carbon;

class ProductResource extends \App\Http\Resources\BaseResource {

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
    */
    public function toArray($request) {
        // $acl = $this->displayAcl("Product");
        setlocale(LC_TIME, 'fr_FR'); 

        //Months list in French
        $monthInFrench = [
            'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
            'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
        ];

        $createMonthNumber = Carbon::parse($this->created_at)->format('n');
        $updateMonthNumber = Carbon::parse($this->updated_at)->format('n');

        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'brand' => $this->brand,
            'dosage' => $this->dosage,

            'conditioning_unit' => new ConditioningUnitResource($this->conditioning_unit),
            'administration_route' => new AdministrationRouteResource($this->administration_route),
            'sale_unit' => new SaleUnitResource($this->sale_unit),
            'category' => new CategoryResource($this->category),
            'type' => new TypeProductResource($this->type),

            // 'user' => new UserResource($this->user),
            'is_synced' => $this->is_synced,
            'deleted_at' => $this->deleted_at,

            'created_at' => Carbon::parse($this->created_at)->formatLocalized('%d ') . $monthInFrench[$createMonthNumber - 1] . Carbon::parse($this->created_at)->formatLocalized(' %Y à %H:%M:%S'),
            'updated_at' => Carbon::parse($this->updated_at)->formatLocalized('%d ') . $monthInFrench[$updateMonthNumber - 1] . Carbon::parse($this->created_at)->formatLocalized(' %Y à %H:%M:%S'),

            // 'acl' => $acl,
        ];
    }

}
