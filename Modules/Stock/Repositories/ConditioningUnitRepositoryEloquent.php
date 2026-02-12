<?php

namespace Modules\Stock\Repositories;

use App\Repositories\AppBaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Contracts\RepositoryInterface;
use Modules\Stock\Entities\ConditioningUnit;

/**
 * Class ConditioningUnitRepositoryEloquent.
 *
 * @package namespace Modules\Stock\Repositories;
 */
class ConditioningUnitRepositoryEloquent extends AppBaseRepository implements RepositoryInterface {

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model() {
        return ConditioningUnit::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot() {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
