<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\Supplier;
use Webpatser\Uuid\Uuid;

class SupplierObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Supplier  $model
     * @return void
     */
    public function creating(Supplier $model) {
        $model->uuid = Uuid::generate();
    }
}
