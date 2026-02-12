<?php

namespace Modules\stock\Observers;

use Modules\Stock\Entities\Product;
use Webpatser\Uuid\Uuid;

class ProductObserver {

    /**
     * Handle to the note "creating" event.
     *
     * @param  Product $model
     * @return void
     */
    public function creating(Product $model) {
        $model->uuid = Uuid::generate();
    }
}
