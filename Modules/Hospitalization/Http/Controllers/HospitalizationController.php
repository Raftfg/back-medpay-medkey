<?php
namespace Modules\Hospitalization\Http\Controllers;

use App\Http\Controllers\Api\V1\ApiController;

class HospitalizationController extends ApiController
{
    public function __construct() {
        parent::__construct();
        $this->moduleAlias = strtolower(config('hospitalization.name'));
        $this->mediaCollectionName = config("{$this->moduleAlias}.media_collection_name");
        $this->mediaDisk = config("{$this->moduleAlias}.media_disk");
    }
}
