<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\SupplyProduct;
use Webpatser\Uuid\Uuid;

class SupplyProductObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  SupplyProduct  $model
     * @return void
     */
    public function creating(SupplyProduct $model) {
        $model->uuid = Uuid::generate();
    }
}
