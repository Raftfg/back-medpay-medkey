<?php

namespace Modules\Absence\Observers;

use Modules\Absence\Entities\Absent;
use Webpatser\Uuid\Uuid;

class AbsentObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  OrdersMission  $model
     * @return void
     */
    public function creating(Absent $model) {
        $model->uuid = Uuid::generate();
    }
}
