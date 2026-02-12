<?php

namespace Modules\Annuaire\Observers;

use Modules\Annuaire\Entities\Experience_pro;
use Webpatser\Uuid\Uuid;

class Experience_proObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Experience_pro  $model
     * @return void
     */
    public function creating(Experience_pro $model) {
        $model->uuid = Uuid::generate();
    }
}
