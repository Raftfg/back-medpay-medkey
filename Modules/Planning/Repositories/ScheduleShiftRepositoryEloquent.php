<?php

namespace Modules\Planning\Repositories;

use App\Repositories\AppBaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Contracts\RepositoryInterface;
use Modules\Planning\Entities\ScheduleShift;

class ScheduleShiftRepositoryEloquent extends AppBaseRepository implements RepositoryInterface
{
    public function model()
    {
        return ScheduleShift::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
