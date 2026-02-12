<?php

namespace Modules\Absence\Observers;

use Modules\Absence\Entities\Mission;
use Webpatser\Uuid\Uuid;

class MissionObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Mission  $model
     * @return void
     */
    public function creating(Mission $model) {
        $model->uuid = Uuid::generate();
    }
}
