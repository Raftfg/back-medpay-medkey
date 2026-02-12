<?php

namespace Modules\Notifier\Observers;

use Modules\Notifier\Entities\NotifierTracking;
use Webpatser\Uuid\Uuid;

class NotifierTrackingObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  NotifierTracking  $model
     * @return void
     */
    public function creating(NotifierTracking $model) {
        $model->uuid = Uuid::generate();
    }
}
