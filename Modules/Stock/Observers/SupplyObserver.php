<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\Supply;
use Webpatser\Uuid\Uuid;

class SupplyObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Supply  $model
     * @return void
     */
    public function creating(Supply $model) {
        $model->uuid = Uuid::generate();
    }
}
