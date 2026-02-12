<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\SaleProduct;
use Webpatser\Uuid\Uuid;

class SaleProductObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  SaleProduct $model
     * @return void
     */
    public function creating(SaleProduct $model) {
        $model->uuid = Uuid::generate();
    }
}
