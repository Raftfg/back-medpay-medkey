<?php

namespace Modules\Administration\Observers;

use Modules\Administration\Entities\Insurance;
use Webpatser\Uuid\Uuid;

class InsuranceObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  OrdersMission  $model
     * @return void
     */
    public function creating(Insurance $model) {
        $model->uuid = Uuid::generate();
    }
}