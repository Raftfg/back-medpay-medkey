<?php

namespace Modules\User\Observers;

use Modules\User\Entities\User;
use Webpatser\Uuid\Uuid;

class UserObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  User  $model
     * @return void
     */
    public function creating(User $model) {
        $model->uuid = Uuid::generate();
    }
}
