<?php

namespace Modules\Planning\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Planning\Http\Controllers\PlanningController;
use Modules\Planning\Repositories\WorkScheduleRepositoryEloquent;
use Modules\Planning\Http\Resources\WorkScheduleResource;
use Modules\Planning\Http\Resources\WorkSchedulesResource;
use Modules\Planning\Http\Requests\WorkScheduleIndexRequest;
use Modules\Planning\Http\Requests\WorkScheduleStoreRequest;
use Modules\Planning\Http\Requests\WorkScheduleUpdateRequest;
use Modules\Planning\Http\Requests\WorkScheduleDeleteRequest;
use Modules\Planning\Entities\WorkSchedule;

class WorkScheduleController extends PlanningController
{
    protected $workScheduleRepositoryEloquent;

    public function __construct(WorkScheduleRepositoryEloquent $workScheduleRepositoryEloquent)
    {
        parent::__construct();
        $this->workScheduleRepositoryEloquent = $workScheduleRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(WorkScheduleIndexRequest $request)
    {
        // Utiliser la query Eloquent directement (plus fiable que newQuery() sur repository)
        $query = WorkSchedule::query();
        
        // Filtres optionnels
        if ($request->has('employer_id')) {
            $query->where('employer_id', $request->employer_id);
        }
        
        if ($request->has('service_id')) {
            $query->where('service_id', $request->service_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->forPeriod($request->start_date, $request->end_date);
        }
        
        $donnees = $query->with(['employer', 'service', 'shifts'])->paginate($this->nombrePage);
        return new WorkSchedulesResource($donnees);
    }

    /**
     * Store a newly created resource.
     */
    public function store(WorkScheduleStoreRequest $request)
    {
        $attributs = $request->all();
        
        $item = DB::transaction(function () use ($attributs) {
            $item = $this->workScheduleRepositoryEloquent->create($attributs);
            return $item;
        });
        
        $item = $item->fresh(['employer', 'service', 'shifts']);
        return new WorkScheduleResource($item);
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkScheduleIndexRequest $request, $uuid)
    {
        $item = $this->workScheduleRepositoryEloquent->findByUuidOrFail($uuid)->first();
        $item->load(['employer', 'service', 'shifts.employer', 'shifts.service']);
        return new WorkScheduleResource($item);
    }

    /**
     * Update the specified resource.
     */
    public function update(WorkScheduleUpdateRequest $request, $uuid)
    {
        $workSchedule = $this->workScheduleRepositoryEloquent->findByUuidOrFail($uuid)->first();
        $attributs = $request->all();
        
        $item = DB::transaction(function () use ($attributs, $workSchedule) {
            $item = $this->workScheduleRepositoryEloquent->update($attributs, $workSchedule->id);
            return $item;
        });
        
        $item = $item->fresh(['employer', 'service', 'shifts']);
        return new WorkScheduleResource($item);
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(WorkScheduleDeleteRequest $request, $uuid)
    {
        $workSchedule = $this->workScheduleRepositoryEloquent->findByUuidOrFail($uuid)->first();
        $this->workScheduleRepositoryEloquent->delete($workSchedule->id);
        
        $data = [
            "message" => __("Planning supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }

    /**
     * Publier un planning
     */
    public function publish(Request $request, $uuid)
    {
        $workSchedule = $this->workScheduleRepositoryEloquent->findByUuidOrFail($uuid)->first();
        
        // Vérifier la couverture des services
        // Vérifier les durées légales
        // Notifier le personnel
        
        $workSchedule->update([
            'status' => 'published',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        
        return new WorkScheduleResource($workSchedule->fresh());
    }
}
