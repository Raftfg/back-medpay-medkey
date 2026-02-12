<?php

namespace Modules\Administration\Observers;

use Modules\Administration\Entities\ProductType;
use Webpatser\Uuid\Uuid;

class ProductTypeObserver
{

    /**
     * Handle to the note "creating" event.
     *
     * @param  OrdersMission  $model
     * @return void
     */
    public function creating(ProductType $model)
    {
        $model->uuid = Uuid::generate();
    }
}
