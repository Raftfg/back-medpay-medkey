<?php

namespace Modules\Acl\Observers;

use Modules\Acl\Entities\User;
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

    /**
     * Handle to the note "creating" event.
     *
     * @param  User  $model
     * @return void
     */
    public function created(User $model) {
    }

    /**
     * Handle to the note "updated" event.
     *
     * @param  User  $model
     * @return void
     */
    public function updated(User $model) {
    }

    /**
     * Handle to the note "deleted" event.
     *
     * @param  User  $model
     * @return void
     */
    public function deleted(User $model) {
    }
 
    /**
     * Handle the User "restored" event.
     *
     * @param  User  $model
     * @return void
     */
    public function restored(User $model)
    {
    }
 
    /**
     * Handle the User "forceDeleted" event.
     *
     * @param  User  $model
     * @return void
     */
    public function forceDeleted(User $model)
    {
    }
}
