<?php

namespace Modules\Administration\Observers;

use Webpatser\Uuid\Uuid;
use Modules\Administration\Entities\Pays;
use Modules\Administration\Entities\Insurance;

class PaysObserver
{

    /**
     * Handle to the note "creating" event.
     *
     * @param  OrdersMission  $model
     * @return void
     */
    public function creating(Pays $model)
    {
        $model->uuid = Uuid::generate();
    }
}
