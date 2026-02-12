<?php

namespace Modules\Annuaire\Observers;

use Modules\Annuaire\Entities\Certification;
use Webpatser\Uuid\Uuid;

class CertificationObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Certification  $model
     * @return void
     */
    public function creating(Certification $model) {
        $model->uuid = Uuid::generate();
    }
}
