<?php

namespace Modules\Hospitalization\Observers;

use Modules\Hospitalization\Entities\Bed;
use Webpatser\Uuid\Uuid;

class BedObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Bed  $model
     * @return void
     */
    public function creating(Bed $model) {
        $model->uuid = Uuid::generate();
    }
}
