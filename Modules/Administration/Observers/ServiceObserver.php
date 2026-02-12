<?php

namespace Modules\Administration\Observers;
use Modules\Administration\Entities\Service;
use Webpatser\Uuid\Uuid;



class ServiceObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Service  $model
     * @return void
     */
    public function creating(Service $model) {
        $model->uuid = Uuid::generate();
    }
}
