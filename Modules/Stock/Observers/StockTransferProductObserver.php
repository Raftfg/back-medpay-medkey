<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\StockTransferProduct;
use Webpatser\Uuid\Uuid;

class StockTransferProductObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  StockTransferProduct  $model
     * @return void
     */
    public function creating(StockTransferProduct $model) {
        $model->uuid = Uuid::generate();
    }
}
