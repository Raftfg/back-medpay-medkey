<?php

namespace Modules\Administration\Observers;

use Modules\Administration\Entities\Pack;
use Webpatser\Uuid\Uuid;

class PackObserver
{

    /**
     * Handle to the note "creating" event.
     *
     * @param  OrdersMission  $model
     * @return void
     */
    public function creating(Pack $model)
    {
        $model->uuid = Uuid::generate();
    }
}
