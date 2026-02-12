<?php

namespace Modules\Stock\Repositories;

use App\Repositories\AppBaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Contracts\RepositoryInterface;
use Modules\Stock\Entities\StockTransfer;

/**
 * Class StockTransferRepositoryEloquent.
 *
 * @package namespace Modules\Stock\Repositories;
 */
class StockTransferRepositoryEloquent extends AppBaseRepository implements RepositoryInterface {

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model() {
        return StockTransfer::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot() {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
