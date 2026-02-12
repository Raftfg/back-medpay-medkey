<?php

namespace Modules\Notifier\Repositories;

use App\Repositories\AppBaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Contracts\RepositoryInterface;
use Modules\Notifier\Entities\NotifierTracking;
/**
 * Class TenantRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class NotifierTrackingRepositoryEloquent extends AppBaseRepository implements RepositoryInterface {

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model() {
        return NotifierTracking::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot() {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    

}
