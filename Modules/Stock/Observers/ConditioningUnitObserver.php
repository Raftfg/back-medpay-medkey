<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\ConditioningUnit;
use Webpatser\Uuid\Uuid;

class ConditioningUnitObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  ConditioningUnit  $model
     * @return void
     */
    public function creating(ConditioningUnit $model) {
        $model->uuid = Uuid::generate();
    }
}
