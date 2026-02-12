<?php

namespace Modules\Movment\Repositories;

use App\Repositories\AppBaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Contracts\RepositoryInterface;
use Modules\Movment\Entities\Allergie;

class AllergieRepositoryEloquent extends AppBaseRepository implements RepositoryInterface {

    public function model() {
        return Allergie::class;
    }

    public function boot() {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
