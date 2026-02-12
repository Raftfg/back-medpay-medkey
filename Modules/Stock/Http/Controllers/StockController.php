<?php

namespace Modules\Stock\Http\Controllers;

use App\Http\Controllers\Api\V1\ApiController;

class StockController extends ApiController
{
    public function __construct() {
        parent::__construct();
        $this->moduleAlias = strtolower(config('stock.name'));
        $this->mediaCollectionName = config("{$this->moduleAlias}.media_collection_name");
        $this->mediaDisk = config("{$this->moduleAlias}.media_disk");
    }
}
