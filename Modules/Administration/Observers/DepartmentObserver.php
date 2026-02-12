<?php

namespace Modules\Administration\Observers;
use Modules\Administration\Entities\Department;
use Webpatser\Uuid\Uuid;



class DepartmentObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Department  $model
     * @return void
     */
    public function creating(Department $model) {
        $model->uuid = Uuid::generate();
    }
}
