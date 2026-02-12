<?php

namespace Modules\Annuaire\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Annuaire\Http\Controllers\AnnuaireController;
use Modules\Annuaire\Repositories\EmployerPerformanceRepositoryEloquent;
use Modules\Annuaire\Http\Resources\EmployerPerformanceResource;
use Modules\Annuaire\Http\Resources\EmployerPerformancesResource;
use Modules\Annuaire\Http\Requests\EmployerPerformanceIndexRequest;
use Modules\Annuaire\Http\Requests\EmployerPerformanceStoreRequest;
use Modules\Annuaire\Http\Requests\EmployerPerformanceUpdateRequest;
use Modules\Annuaire\Http\Requests\EmployerPerformanceDeleteRequest;

class EmployerPerformanceController extends AnnuaireController
{
    protected $employerPerformanceRepositoryEloquent;

    public function __construct(EmployerPerformanceRepositoryEloquent $employerPerformanceRepositoryEloquent)
    {
        parent::__construct();
        $this->employerPerformanceRepositoryEloquent = $employerPerformanceRepositoryEloquent;
    }

    public function index(EmployerPerformanceIndexRequest $request)
    {
        $query = $this->employerPerformanceRepositoryEloquent->makeModel()->newQuery();

        if ($request->filled('employers_id')) {
            $query->where('employers_id', $request->employers_id);
        }
        if ($request->filled('services_id')) {
            $query->where('services_id', $request->services_id);
        }
        if ($request->filled('period_type')) {
            $query->where('period_type', $request->period_type);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
            });
        }

        $donnees = $query->with(['employer', 'service'])->paginate($this->nombrePage);

        return new EmployerPerformancesResource($donnees);
    }

    public function show(EmployerPerformanceIndexRequest $request, $uuid)
    {
        $item = $this->employerPerformanceRepositoryEloquent->findByUuidOrFail($uuid)->first();
        $item->load(['employer', 'service']);

        return new EmployerPerformanceResource($item);
    }

    public function store(EmployerPerformanceStoreRequest $request)
    {
        $attributes = $request->all();
        $attributes['evaluator_user_id'] = auth()->id();

        $item = DB::transaction(function () use ($attributes) {
            return $this->employerPerformanceRepositoryEloquent->create($attributes);
        });

        $item = $item->fresh(['employer', 'service']);

        return new EmployerPerformanceResource($item);
    }

    public function update(EmployerPerformanceUpdateRequest $request, $uuid)
    {
        $performance = $this->employerPerformanceRepositoryEloquent->findByUuidOrFail($uuid)->first();
        $attributes = $request->all();

        $item = DB::transaction(function () use ($attributes, $performance) {
            return $this->employerPerformanceRepositoryEloquent->update($attributes, $performance->id);
        });

        $item = $item->fresh(['employer', 'service']);

        return new EmployerPerformanceResource($item);
    }

    public function destroy(EmployerPerformanceDeleteRequest $request, $uuid)
    {
        $performance = $this->employerPerformanceRepositoryEloquent->findByUuidOrFail($uuid)->first();
        $this->employerPerformanceRepositoryEloquent->delete($performance->id);

        $data = [
            'message' => __('Évaluation supprimée avec succès'),
        ];

        return reponse_json_transform($data);
    }
}

