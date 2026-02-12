<?php

namespace Modules\Hospitalization\Repositories;

use App\Repositories\AppBaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Contracts\RepositoryInterface;
use Modules\Hospitalization\Entities\Room;

/**
 * Class RoomRepositoryEloquent.
 *
 * @package namespace Modules\Hospitalization\Repositories;
 */
class RoomRepositoryEloquent extends AppBaseRepository implements RepositoryInterface {

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model() {
        return Room::class;
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
