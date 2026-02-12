<?php

namespace Modules\Tracking\Observers;

use Modules\Tracking\Entities\ActivityLog;
use Webpatser\Uuid\Uuid;

class ActivityLogObserver {

    /**
     * Handle to the model "creating" event.
     *
     * @param  ActivityLog  $model
     * @return void
     */
    public function creating(ActivityLog $model) {
        $model->uuid = Uuid::generate();
    }
}
