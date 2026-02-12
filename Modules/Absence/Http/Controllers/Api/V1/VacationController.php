<?php

namespace Modules\Absence\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepositoryEloquent;

use Modules\Absence\Repositories\VacationRepositoryEloquent;
use Modules\Absence\Repositories\TypeVacationRepositoryEloquent;

use Modules\Absence\Http\Resources\VacationResource;
use Modules\Absence\Http\Resources\VacationsResource;

use Modules\Absence\Http\Controllers\AbsenceController;

use Modules\Absence\Http\Requests\VacationIndexRequest;
use Modules\Absence\Http\Requests\VacationStoreRequest;
use Modules\Absence\Http\Requests\VacationDeleteRequest;
use Modules\Absence\Http\Requests\VacationUpdateRequest;


class VacationController extends AbsenceController {

    /**
     * @var PostRepository
     */
    protected $vacationRepositoryEloquent, $userRepositoryEloquent, $typeVacationRepositoryEloquent;

    public function __construct(VacationRepositoryEloquent $vacationRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent, TypeVacationRepositoryEloquent $typeVacationRepositoryEloquent) {
        parent::__construct();
        $this->vacationRepositoryEloquent = $vacationRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
        $this->typeVacationRepositoryEloquent = $typeVacationRepositoryEloquent;
    }
    
   /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(VacationIndexRequest $request)
    {
        $donnees = $this->vacationRepositoryEloquent->with(['user', 'typeVacation'])->paginate($this->nombrePage);
        return new VacationsResource($donnees);
    }
    
   /**
     * Create a resource.
     *
     * @return Response
     */
    public function show(VacationIndexRequest $request, $uuid) {
        $item = $this->vacationRepositoryEloquent->with(['user', 'typeVacation'])->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new VacationResource($item);
    }

    public function store(VacationStoreRequest $request)
    {
        $attributs = $request->all();

        $item = DB::transaction(function () use ($attributs) {
            // Utilisateur concerné (employé) : UUID reçu depuis le front
            $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
            $attributs['users_id'] = $user->id;
            
            // Type de congé (UUID)
            $typeVacation = $this->typeVacationRepositoryEloquent->findByUuid($attributs['type_vacations_id'])->first();
            $attributs['type_vacations_id'] = $typeVacation->id;

            // Qui a créé la demande (employé, chef de service, RH...) : user connecté
            $attributs['created_by'] = auth()->id();

            //department à gérer 

            $item = $this->vacationRepositoryEloquent->create($attributs);

            return $item;
        });

        $item = $item->fresh(['user', 'typeVacation']);
        return new VacationResource($item);
    }
    
   /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(VacationUpdateRequest $request, $uuid)
    {
        $item = $this->vacationRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
        $attributs['users_id'] = $user->id;
        
        $typeVacation = $this->typeVacationRepositoryEloquent->findByUuid($attributs['type_vacations_id'])->first();
        $attributs['type_vacations_id'] = $typeVacation->id;

        //department à gérer 

        $item = $this->vacationRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh(['user', 'typeVacation']);
        return new VacationResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(VacationDeleteRequest $request, $uuid)
    {
        $vacation = $this->vacationRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->vacationRepositoryEloquent->delete($vacation->id);
        
        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }

    /**
     * Approuver une demande de congé/absence
     */
    public function approve(Request $request, $uuid)
    {
        $vacation = $this->vacationRepositoryEloquent->findByUuidOrFail($uuid)->first();

        $item = DB::transaction(function () use ($vacation) {
            return $this->vacationRepositoryEloquent->update([
                'status' => 'approved',
                'decision_chief' => 'approved',
                'reject_reason' => null,
            ], $vacation->id);
        });

        return new VacationResource($item->fresh());
    }

    /**
     * Rejeter une demande de congé/absence
     */
    public function reject(Request $request, $uuid)
    {
        $request->validate([
            'reject_reason' => 'required|string|max:255',
        ]);

        $vacation = $this->vacationRepositoryEloquent->findByUuidOrFail($uuid)->first();

        $item = DB::transaction(function () use ($vacation, $request) {
            return $this->vacationRepositoryEloquent->update([
                'status' => 'rejected',
                'decision_chief' => 'rejected',
                'reject_reason' => $request->reject_reason,
            ], $vacation->id);
        });

        return new VacationResource($item->fresh());
    }    
}
