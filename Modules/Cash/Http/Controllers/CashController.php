<?php

namespace Modules\Cash\Http\Controllers;

use App\Http\Controllers\Api\V1\ApiController;

class CashController extends ApiController
{
    public function __construct() {
        parent::__construct();
        $this->moduleAlias = strtolower(config('cash.name'));
        $this->mediaCollectionName = config("{$this->moduleAlias}.media_collection_name");
        $this->mediaDisk = config("{$this->moduleAlias}.media_disk");
    }
}
