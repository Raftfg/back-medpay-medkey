<?php

namespace Modules\Dashboard\Http\Controllers\Api\V1;

use Illuminate\Http\Response;
use Illuminate\Http\Request;

class DashboardController extends \Modules\Dashboard\Http\Controllers\DashboardController {

    /**
     * @var PostRepository
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return Response
     */
    public function index(Request $request) {
        $data = [
        ];
        return reponse_json_transform($data);
    }

}
