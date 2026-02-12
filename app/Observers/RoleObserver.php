<?php

namespace App\Observers;

use Modules\Acl\Entities\Role;
use Webpatser\Uuid\Uuid;

class RoleObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  User  $model
     * @return void
     */
    public function creating(Role $model) {
        $model->uuid = Uuid::generate();
        if(!$model->name){
            $model->name = \Str::slug($model->display_name);
        }
        if(!$model->guard_name){
            $model->guard_name = guard_web();
        }
    }
}
