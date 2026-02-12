<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\StockProduct;
use Webpatser\Uuid\Uuid;

class StockProductObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  StockProduct  $model
     * @return void
     */
    public function creating(StockProduct $model) {
        $model->uuid = Uuid::generate();
    }
}
