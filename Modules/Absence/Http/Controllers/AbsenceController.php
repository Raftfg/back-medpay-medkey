<?php

namespace Modules\Absence\Http\Controllers;

use App\Http\Controllers\Api\V1\ApiController;

class AbsenceController extends ApiController
{
    public function __construct() {
        parent::__construct();
        $this->moduleAlias = strtolower(config('absence.name'));
        $this->mediaCollectionName = config("{$this->moduleAlias}.media_collection_name");
        $this->mediaDisk = config("{$this->moduleAlias}.media_disk");
    }
}
