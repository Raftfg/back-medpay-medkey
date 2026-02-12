<?php

namespace Modules\Planning\Repositories;

use App\Repositories\AppBaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Contracts\RepositoryInterface;
use Modules\Planning\Entities\WorkSchedule;

class WorkScheduleRepositoryEloquent extends AppBaseRepository implements RepositoryInterface
{
    public function model()
    {
        return WorkSchedule::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
