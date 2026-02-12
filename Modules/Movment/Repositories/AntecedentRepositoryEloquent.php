<?php

namespace Modules\Movment\Repositories;

use App\Repositories\AppBaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Contracts\RepositoryInterface;
use Modules\Movment\Entities\Antecedent;

class AntecedentRepositoryEloquent extends AppBaseRepository implements RepositoryInterface {

    public function model() {
        return Antecedent::class;
    }

    public function boot() {
        $this->pushCriteria(app(RequestCriteria::class));
    }

}
