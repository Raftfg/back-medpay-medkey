<?php

namespace Modules\Annuaire\Observers;

use Modules\Annuaire\Entities\Competence;
use Webpatser\Uuid\Uuid;

class CompetenceObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Competence  $model
     * @return void
     */
    public function creating(Competence $model) {
        $model->uuid = Uuid::generate();
    }
}
