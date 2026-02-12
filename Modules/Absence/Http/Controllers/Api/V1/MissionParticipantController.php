<?php

namespace Modules\Absence\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepositoryEloquent;
use Modules\Absence\Http\Controllers\AbsenceController;
use Modules\Absence\Repositories\MissionRepositoryEloquent;
use Modules\Absence\Http\Resources\MissionParticipantResource;
use Modules\Absence\Http\Resources\MissionParticipantsResource;
use Modules\Absence\Http\Requests\MissionParticipantIndexRequest;
use Modules\Absence\Http\Requests\MissionParticipantStoreRequest;
use Modules\Absence\Http\Requests\MissionParticipantDeleteRequest;
use Modules\Absence\Http\Requests\MissionParticipantUpdateRequest;
use Modules\Absence\Repositories\MissionParticipantRepositoryEloquent;

class MissionParticipantController extends AbsenceController {

    /**
     * @var PostRepository
     */
    
    protected $missionParticipantRepositoryEloquent, $userRepositoryEloquent, $missionRepositoryEloquent;

    public function __construct(MissionParticipantRepositoryEloquent $missionParticipantRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent, MissionRepositoryEloquent $missionRepositoryEloquent) {
        parent::__construct();
        $this->missionParticipantRepositoryEloquent = $missionParticipantRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
        $this->missionRepositoryEloquent = $missionRepositoryEloquent;
    }
    
   /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(MissionParticipantIndexRequest $request)
    {
        $donnees = $this->missionParticipantRepositoryEloquent->paginate($this->nombrePage);
        return new MissionParticipantsResource($donnees);
    }
    
   /**
     * Create a resource.
     *
     * @return Response
     */
    public function show(MissionParticipantIndexRequest $request, $uuid) {
        $item = $this->missionParticipantRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new MissionParticipantResource($item);
    }

    public function store(MissionParticipantStoreRequest $request)
    {
        $attributs = $request->all();

        $item = DB::transaction(function () use ($attributs) {
            $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
            $attributs['users_id'] = $user->id;

            $mission = $this->missionRepositoryEloquent->findByUuid($attributs['missions_id'])->first();
            $attributs['missions_id'] = $mission->id;

            $item = $this->missionParticipantRepositoryEloquent->create($attributs);

            return $item;
        });

        $item = $item->fresh();

        return new MissionParticipantResource($item);
    }
    
   /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(MissionParticipantUpdateRequest $request, $uuid)
    {
        $item = $this->missionParticipantRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        
        $attributs = $request->all();

        $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
        $attributs['users_id'] = $user->id;

        $mission = $this->missionRepositoryEloquent->findByUuid($attributs['missions_id'])->first();
        $attributs['missions_id'] = $mission->id;

        $item = $this->missionParticipantRepositoryEloquent->update($attributs, $item->id);

        $item = $item->fresh();
        return new MissionParticipantResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(MissionParticipantDeleteRequest $request, $uuid)
    {
        $missionParticipant = $this->missionParticipantRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->missionParticipantRepositoryEloquent->delete($missionParticipant->id);
        
        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }    
}
