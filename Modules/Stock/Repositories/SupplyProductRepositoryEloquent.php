<?php

namespace Modules\Stock\Repositories;

use App\Repositories\AppBaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Contracts\RepositoryInterface;
use Modules\Stock\Entities\SupplyProduct;

/**
 * Class SupplyProductRepositoryEloquent.
 *
 * @package namespace Modules\Stock\Repositories;
 */
class SupplyProductRepositoryEloquent extends AppBaseRepository implements RepositoryInterface {

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model() {
        return SupplyProduct::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot() {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function findByLotNumberAndSupply($lotNumber, $supplyId)
    {
        return $this->model->where('lot_number', $lotNumber)
            ->where('supply_id', $supplyId)
            ->first();
    }

    public function findByProductAndSupply($productId, $supplyId)
    {
        return $this->model->where('product_id', $productId)
            ->where('supply_id', $supplyId)
            ->first();
    }

}
