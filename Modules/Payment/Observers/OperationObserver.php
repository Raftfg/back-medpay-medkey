<?php

namespace Modules\Payment\Observers;

use Modules\Payment\Entities\Operation;
use Webpatser\Uuid\Uuid;

class OperationObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Operation  $model
     * @return void
     */
    public function creating(Operation $model) {
        $model->uuid = Uuid::generate();
    }
}
