<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Api\V1\ApiController;

class PaymentController extends ApiController
{
    public function __construct() {
        parent::__construct();
        $this->moduleAlias = strtolower(config('payment.name'));
        $this->mediaCollectionName = config("{$this->moduleAlias}.media_collection_name");
        $this->mediaDisk = config("{$this->moduleAlias}.media_disk");
    }
}
