<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\Store;
use Webpatser\Uuid\Uuid;

class StoreObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Store  $model
     * @return void
     */
    public function creating(Store $model) {
        $model->uuid = Uuid::generate();
    }
}
