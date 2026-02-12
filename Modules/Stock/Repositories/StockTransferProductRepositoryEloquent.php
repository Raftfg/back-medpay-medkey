<?php

namespace Modules\Stock\Repositories;

use App\Repositories\AppBaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Contracts\RepositoryInterface;
use Modules\Stock\Entities\StockTransferProduct;

/**
 * Class StockTransferProductRepositoryEloquent.
 *
 * @package namespace Modules\Stock\Repositories;
 */
class StockTransferProductRepositoryEloquent extends AppBaseRepository implements RepositoryInterface {

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model() {
        return StockTransferProduct::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot() {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
