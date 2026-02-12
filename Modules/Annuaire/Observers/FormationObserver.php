<?php

namespace Modules\Annuaire\Observers;

use Modules\Annuaire\Entities\Formation;
use Webpatser\Uuid\Uuid;

class FormationObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Formation  $model
     * @return void
     */
    public function creating(Formation $model) {
        $model->uuid = Uuid::generate();
    }
}
