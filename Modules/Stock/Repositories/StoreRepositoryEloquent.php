<?php

namespace Modules\Stock\Repositories;

use App\Repositories\AppBaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Contracts\RepositoryInterface;
use Modules\Stock\Entities\Store;

/**
 * Class StoreRepositoryEloquent.
 *
 * @package namespace Modules\Stock\Repositories;
 */
class StoreRepositoryEloquent extends AppBaseRepository implements RepositoryInterface {

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model() {
        return Store::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot() {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function findByCode($code)
    {
        return $this->model->where('code', $code)->first();
    }
}
