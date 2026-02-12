<?php

namespace Modules\Annuaire\Http\Controllers;

use App\Http\Controllers\Api\V1\ApiController;

class AnnuaireController extends ApiController
{
    public function __construct() {
        parent::__construct();
        $this->moduleAlias = strtolower(config('annuaire.name'));
        $this->mediaCollectionName = config("{$this->moduleAlias}.media_collection_name");
        $this->mediaDisk = config("{$this->moduleAlias}.media_disk");
    }
}
