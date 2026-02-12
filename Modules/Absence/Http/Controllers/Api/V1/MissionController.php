<?php

namespace Modules\Absence\Http\Controllers\Api\V1;

use App\Repositories\UserRepositoryEloquent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Absence\Http\Resources\MissionResource;
use Modules\Absence\Http\Resources\MissionsResource;
use Modules\Absence\Http\Requests\MissionIndexRequest;
use Modules\Absence\Http\Requests\MissionStoreRequest;
use Modules\Absence\Http\Controllers\AbsenceController;
use Modules\Absence\Http\Requests\MissionDeleteRequest;
use Modules\Absence\Http\Requests\MissionUpdateRequest;
use Modules\Absence\Repositories\MissionRepositoryEloquent;

class MissionController extends AbsenceController {

    /**
     * @var PostRepository
     */
    protected $missionRepositoryEloquent, $userRepositoryEloquent;

    public function __construct(MissionRepositoryEloquent $missionRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent) {
        parent::__construct();
        $this->missionRepositoryEloquent = $missionRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }
    
   /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(MissionIndexRequest $request)
    {
        $donnees = $this->missionRepositoryEloquent->paginate($this->nombrePage);
        return new MissionsResource($donnees);
    }
    
   /**
     * Create a resource.
     *
     * @return Response
     */
    public function show(MissionIndexRequest $request, $uuid) {
        $item = $this->missionRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new MissionResource($item);
    }

    public function store(MissionStoreRequest $request)
    {
        $attributs = $request->all();

        $item = DB::transaction(function () use ($attributs) {
            $user = $this->userRepositoryEloquent->findByUuid($attributs['mission_head_id'])->first();
            $attributs['mission_head_id'] = $user->id;

            //departement à gérer

            $item = $this->missionRepositoryEloquent->create($attributs);

            return $item;
        });

        $item = $item->fresh();
        return new MissionResource($item);
    }
    
   /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(MissionUpdateRequest $request, $uuid)
    {
        $item = $this->missionRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        $user = $this->userRepositoryEloquent->findByUuid($attributs['mission_head_id'])->first();
        $attributs['mission_head_id'] = $user->id;

        //departement à gérer 

        $item = $this->missionRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();
        return new MissionResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(MissionDeleteRequest $request, $uuid)
    {
        $mission = $this->missionRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->missionRepositoryEloquent->delete($mission->id);
        
        $data = [
            "message" => __("Mission supprimée avec succès"),
        ];
        return reponse_json_transform($data);
    }    
}
