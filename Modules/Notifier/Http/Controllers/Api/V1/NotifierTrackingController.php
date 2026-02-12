<?php

namespace Modules\Notifier\Http\Controllers\Api\V1;

use Illuminate\Http\Response;
use Modules\Notifier\Http\Controllers\NotifierController;
use Modules\Notifier\Http\Requests\NotifierTrackingIndexRequest;
use Modules\Notifier\Http\Resources\NotifierTrackingsResource;
use Modules\Notifier\Repositories\NotifierTrackingRepositoryEloquent;

class NotifierTrackingController extends NotifierController {

    /**
     * @var PostRepository
     */
    protected $notifierTrackingRepositoryEloquent;
    public function __construct(NotifierTrackingRepositoryEloquent $notifierTrackingRepositoryEloquent
    ) {
        parent::__construct();
        $this->notifierTrackingRepositoryEloquent = $notifierTrackingRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(NotifierTrackingIndexRequest $request) {
        $donnees = $this->notifierTrackingRepositoryEloquent->paginate($this->nombrePage);
        return new NotifierTrackingsResource($donnees);
    }

}
