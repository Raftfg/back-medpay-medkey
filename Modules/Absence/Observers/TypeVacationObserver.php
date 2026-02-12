<?php

namespace Modules\Absence\Observers;

use Modules\Absence\Entities\TypeVacation;
use Webpatser\Uuid\Uuid;

class TypeVacationObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  TypeVacation  $model
     * @return void
     */
    public function creating(TypeVacation $model) {
        $model->uuid = Uuid::generate();
    }
}
