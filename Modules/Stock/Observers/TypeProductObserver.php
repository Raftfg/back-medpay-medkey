<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\TypeProduct;
use Webpatser\Uuid\Uuid;

class TypeProductObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  TypeProduct  $model
     * @return void
     */
    public function creating(TypeProduct $model) {
        $model->uuid = Uuid::generate();
    }
}
