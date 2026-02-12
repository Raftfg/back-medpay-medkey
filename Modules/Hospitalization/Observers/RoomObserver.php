<?php

namespace Modules\Hospitalization\Observers;

use Modules\Hospitalization\Entities\Room;
use Webpatser\Uuid\Uuid;

class RoomObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Room  $model
     * @return void
     */
    public function creating(Room $model) {
        $model->uuid = Uuid::generate();
    }
}