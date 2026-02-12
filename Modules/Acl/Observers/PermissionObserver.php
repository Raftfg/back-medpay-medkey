<?php

namespace Modules\Acl\Observers;

use Modules\Acl\Entities\Permission;
use Webpatser\Uuid\Uuid;

class PermissionObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  User  $model
     * @return void
     */
    public function creating(Permission $model) {
        $model->uuid = Uuid::generate();
    }
}
