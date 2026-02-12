<?php

namespace Modules\Planning\Http\Controllers;

use App\Http\Controllers\Api\V1\ApiController;

class PlanningController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->moduleAlias = strtolower(config('planning.name'));
        $this->mediaCollectionName = config("{$this->moduleAlias}.media_collection_name");
        $this->mediaDisk = config("{$this->moduleAlias}.media_disk");
    }
}
