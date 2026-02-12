<?php

namespace Modules\Cash\Observers;

use Modules\Cash\Entities\CashRegister;
use Webpatser\Uuid\Uuid;

class CashRegisterObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  CashRegister  $model
     * @return void
     */
    public function creating(CashRegister $model) {
        $model->uuid = Uuid::generate();
    }
}
