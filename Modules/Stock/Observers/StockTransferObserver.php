<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\StockTransfer;
use Webpatser\Uuid\Uuid;

class StockTransferObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  StockTransfer  $model
     * @return void
     */
    public function creating(StockTransfer $model) {
        $model->uuid = Uuid::generate();
    }
}
