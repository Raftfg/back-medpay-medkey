<?php

namespace Modules\Absence\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepositoryEloquent;
use Modules\Absence\Http\Resources\AbsentResource;
use Modules\Absence\Http\Resources\AbsentsResource;
use Modules\Absence\Http\Requests\AbsentIndexRequest;
use Modules\Absence\Http\Requests\AbsentStoreRequest;
use Modules\Absence\Http\Requests\AbsentDeleteRequest;
use Modules\Absence\Http\Requests\AbsentUpdateRequest;
use Modules\Absence\Http\Controllers\AbsenceController;
use Modules\Absence\Repositories\AbsentRepositoryEloquent;
use Modules\Absence\Repositories\MissionRepositoryEloquent;
use Modules\Absence\Repositories\VacationRepositoryEloquent;

class AbsentController extends AbsenceController {

    /**
     * @var PostRepository
     */
    protected $absentRepositoryEloquent, $userRepositoryEloquent, $missionRepositoryEloquent, $vacationRepositoryEloquent;

    public function __construct(AbsentRepositoryEloquent $absentRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent, MissionRepositoryEloquent $missionRepositoryEloquent, VacationRepositoryEloquent $vacationRepositoryEloquent) {
        parent::__construct();
        $this->absentRepositoryEloquent = $absentRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
        $this->missionRepositoryEloquent = $missionRepositoryEloquent;
        $this->vacationRepositoryEloquent = $vacationRepositoryEloquent;
    }
    
   /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(AbsentIndexRequest $request)
    {
        $donnees = $this->absentRepositoryEloquent->paginate($this->nombrePage);
        return new AbsentsResource($donnees);
    }

    /**
     * Show a resource.
     *
     * @return Response
     */
    public function show(AbsentIndexRequest $request, $uuid) {
        $item = $this->absentRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new AbsentResource($item);
    }
    
   /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(AbsentStoreRequest $request)
    {
        $attributs = $request->all();

        $item = DB::transaction(function () use ($attributs) {
            $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
            $attributs['users_id'] = $user->id;

            if (isset($attributs['missions_id'])) {
                $mission = $this->missionRepositoryEloquent->findByUuid($attributs['missions_id'])->first();
                $attributs['missions_id'] = $mission->id;
            }
            
            if (isset($attributs['vacations_id'])) {
                $vacation = $this->vacationRepositoryEloquent->findByUuid($attributs['vacations_id'])->first();
                $attributs['vacations_id'] = $vacation->id;
            }

            $item = $this->absentRepositoryEloquent->create($attributs);

            return $item;
        });

        $item = $item->fresh();

        return new AbsentResource($item);
    }
    
   /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(AbsentUpdateRequest $request, $uuid)
    {
        $item = $this->absentRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
        $attributs['users_id'] = $user->id;

        if (isset($attributs['missions_id'])) {
            $mission = $this->missionRepositoryEloquent->findByUuid($attributs['missions_id'])->first();
            $attributs['missions_id'] = $mission->id;
        }
        
        if (isset($attributs['vacations_id'])) {
            $vacation = $this->vacationRepositoryEloquent->findByUuid($attributs['vacations_id'])->first();
            $attributs['vacations_id'] = $vacation->id;
        }

        $item = $this->absentRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();
        return new AbsentResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(AbsentDeleteRequest $request, $uuid)
    {
        $absent = $this->absentRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->absentRepositoryEloquent->delete($absent->id);
        
        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }    
}
