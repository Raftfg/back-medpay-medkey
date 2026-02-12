<?php

namespace Modules\Tracking\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\ApiController;
use Illuminate\Http\Response;
use Modules\Tracking\Http\Resources\ActivityLogsResource;
use Modules\Tracking\Http\Resources\ActivityLogResource;
use Modules\Tracking\Repositories\ActivityLogRepositoryEloquent;
use Modules\Tracking\Http\Requests\ActivityLogIndexRequest;

class ActivityLogController extends \Modules\Tracking\Http\Controllers\TrackingController {

    /**
     * @var PostRepository
     */
    protected $activityLogRepositoryEloquent;

    public function __construct(ActivityLogRepositoryEloquent $activityLogRepositoryEloquent) {
        parent::__construct();
        $this->activityLogRepositoryEloquent = $activityLogRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(ActivityLogIndexRequest $request) {
        $queryBuilder = filtre_recherche_builder($request->recherche,
                $this->activityLogRepositoryEloquent->getModel(),
                $this->activityLogRepositoryEloquent->query());
        $donnees = $queryBuilder->orderBy('created_at', 'DESC')
                ->paginate($this->nombrePage);
        return new ActivityLogsResource($donnees);
    }

   /**
     * Show a resource.
     *
     * @return Response
     */
    public function show(ActivityLogIndexRequest $request, $uuid)
    {
        $item = $this->activityLogRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new ActivityLogResource($item);
    }
}
