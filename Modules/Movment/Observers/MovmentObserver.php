<?php

namespace Modules\Movment\Observers;

use Modules\Movment\Entities\Movment;
use Webpatser\Uuid\Uuid;

class MovmentObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Movment  $model
     * @return void
     */
    
    public function creating(Movment $model) {
        $model->uuid = Uuid::generate();
    }
}
