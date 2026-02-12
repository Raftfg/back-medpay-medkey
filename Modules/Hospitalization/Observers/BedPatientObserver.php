<?php

namespace Modules\Hospitalization\Observers;

use Modules\Hospitalization\Entities\BedPatient;
use Webpatser\Uuid\Uuid;

class BedPatientObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  BedPatient  $model
     * @return void
     */
    public function creating(BedPatient $model) {
        $model->uuid = Uuid::generate();
    }
}