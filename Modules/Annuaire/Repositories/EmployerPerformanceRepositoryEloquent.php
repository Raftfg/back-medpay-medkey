<?php

namespace Modules\Annuaire\Repositories;

use App\Repositories\AppBaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Contracts\RepositoryInterface;
use Modules\Annuaire\Entities\EmployerPerformance;

class EmployerPerformanceRepositoryEloquent extends AppBaseRepository implements RepositoryInterface
{
    public function model()
    {
        return EmployerPerformance::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}

