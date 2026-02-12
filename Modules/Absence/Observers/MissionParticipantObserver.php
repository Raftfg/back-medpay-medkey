<?php

namespace Modules\Absence\Observers;

use Modules\Absence\Entities\MissionParticipant;
use Webpatser\Uuid\Uuid;

class MissionParticipantObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  MissionsParticipant  $model
     * @return void
     */
    public function creating(MissionParticipant $model) {
        $model->uuid = Uuid::generate();
    }
}
