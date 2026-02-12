<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\SaleUnit;
use Webpatser\Uuid\Uuid;

class SaleUnitObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  SaleUnit  $model
     * @return void
     */
    public function creating(SaleUnit $model) {
        $model->uuid = Uuid::generate();
    }
}
