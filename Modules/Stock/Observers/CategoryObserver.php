<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\Category;
use Webpatser\Uuid\Uuid;

class CategoryObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Category  $model
     * @return void
     */
    public function creating(Category $model) {
        $model->uuid = Uuid::generate();
    }
}
