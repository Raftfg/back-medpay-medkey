<?php

namespace Modules\Absence\Observers;

use Modules\Absence\Entities\Vacation;
use Webpatser\Uuid\Uuid;

class VacationObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Vacation  $model
     * @return void
     */
    public function creating(Vacation $model) {
        $model->uuid = Uuid::generate();
    }
}
