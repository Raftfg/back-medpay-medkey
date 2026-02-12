<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\AdministrationRoute;
use Webpatser\Uuid\Uuid;

class AdministrationRouteObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  AdministrationRoute  $model
     * @return void
     */
    public function creating(AdministrationRoute $model) {
        $model->uuid = Uuid::generate();
    }
}
