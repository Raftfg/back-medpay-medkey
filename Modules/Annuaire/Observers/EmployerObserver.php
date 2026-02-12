<?php

namespace Modules\Annuaire\Observers;
use Modules\Annuaire\Entities\Employer;
use Webpatser\Uuid\Uuid;



class EmployerObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Employer  $model
     * @return void
     */
    public function creating(Employer $model) {
        $model->uuid = Uuid::generate();
    }
}
