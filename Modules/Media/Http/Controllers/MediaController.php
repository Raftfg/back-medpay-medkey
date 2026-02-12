<?php

namespace Modules\Media\Http\Controllers;

use App\Http\Controllers\Api\V1\ApiController;

class MediaController extends ApiController
{
    public function __construct() {
        parent::__construct();
        $this->moduleAlias = strtolower(config('filiere.name'));
        $this->mediaCollectionName = config("{$this->moduleAlias}.media_collection_name");
        $this->mediaDisk = config("{$this->moduleAlias}.media_disk");
    }
}
