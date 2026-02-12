<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\Stock;
use Webpatser\Uuid\Uuid;

class StockObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Stock  $model
     * @return void
     */
    public function creating(Stock $model) {
        $model->uuid = Uuid::generate();
    }
}
