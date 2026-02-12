<?php

namespace Modules\Annuaire\Observers;
use Modules\Annuaire\Entities\Contrat;
use Webpatser\Uuid\Uuid;



class ContratObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Contrat  $model
     * @return void
     */
    public function creating(Contrat $model) {
        $model->uuid = Uuid::generate();
    }
}
