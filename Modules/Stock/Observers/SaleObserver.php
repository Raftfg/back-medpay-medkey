<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\Sale;
use Webpatser\Uuid\Uuid;

class SaleObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Sale  $model
     * @return void
     */
    public function creating(Sale $model) {
        $model->uuid = Uuid::generate();
    }
}
