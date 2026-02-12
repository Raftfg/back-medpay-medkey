<?php

namespace Modules\Annuaire\Http\Controllers\Api\V1;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Administration\Repositories\DepartmentRepositoryEloquent;
use Modules\Administration\Repositories\ServiceRepositoryEloquent;
use Modules\Administration\Repositories\InsuranceRepositoryEloquent;
use Modules\Annuaire\Http\Resources\EmployerResource;
use Modules\Annuaire\Http\Resources\EmployersResource;
use Modules\Annuaire\Http\Requests\EmployerIndexRequest;
use Modules\Annuaire\Http\Requests\EmployerStoreRequest;
use Modules\Annuaire\Http\Controllers\AnnuaireController;
use Modules\Annuaire\Http\Requests\EmployerDeleteRequest;
use Modules\Annuaire\Http\Requests\EmployerUpdateRequest;
use Modules\Annuaire\Repositories\EmployerRepositoryEloquent;

class EmployerController extends AnnuaireController {

    /**
     * @var PostRepository
     */
    protected $employerRepositoryEloquent, $serviceRepositoryEloquent, $insuranceRepositoryEloquent, $departmentRepositoryEloquent;

    public function __construct(EmployerRepositoryEloquent $employerRepositoryEloquent, ServiceRepositoryEloquent $serviceRepositoryEloquent,
    InsuranceRepositoryEloquent $insuranceRepositoryEloquent,DepartmentRepositoryEloquent $departmentRepositoryEloquent ) {
        parent::__construct();
        $this->employerRepositoryEloquent = $employerRepositoryEloquent;
        $this->serviceRepositoryEloquent = $serviceRepositoryEloquent;
        $this->insuranceRepositoryEloquent = $insuranceRepositoryEloquent;
        $this->departmentRepositoryEloquent = $departmentRepositoryEloquent;

    }

   /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(EmployerIndexRequest $request)
    {
        $donnees = $this->employerRepositoryEloquent->with(['service', 'department'])->paginate($this->nombrePage);
        return new EmployersResource($donnees);
    }

    public function search($request)
    {
        $donnees = $this->employerRepositoryEloquent->where('last_name', 'like', "%$request%")
        ->orWhere('first_name', 'like', "%$request%")->orWhere('email', 'like', "%$request%")
        ->orWhere('sex', 'like', "%$request%")->orWhere('phone_number', 'like', "%$request%")
        ->orderBy('created_at', 'desc')->paginate($this->nombrePage);
        return new EmployersResource($donnees);
    }


    public function show(EmployerIndexRequest $request, $uuid) {
        $item = $this->employerRepositoryEloquent->with(['service', 'department'])->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new EmployerResource($item);
    }



   /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(EmployerStoreRequest $request)
    {
        $attributs = $request->all();

        $item = DB::transaction(function () use ($attributs) {

            if (isset($attributs['departments_id'])) {
                $departments = $this->departmentRepositoryEloquent->findByUuid($attributs['departments_id'])->first();
                $attributs['departments_id'] = $departments->id;
            }

            if (isset($attributs['services_id'])) {
                $services = $this->serviceRepositoryEloquent->findByUuid($attributs['services_id'])->first();
                $attributs['services_id'] = $services->id;
            }

            $item = $this->employerRepositoryEloquent->create($attributs);

            return $item;
        });

        $item = $item->fresh();
        return new EmployerResource($item);
    }

   /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(EmployerUpdateRequest $request, $uuid)
    {
        $employer = $this->employerRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();
        
        $item = DB::transaction(function () use ($attributs, $employer) {
            if (isset($attributs['departments_id'])) {
                $departments = $this->departmentRepositoryEloquent->findByUuid($attributs['departments_id'])->first();
                $attributs['departments_id'] = $departments->id;
            }

            if (isset($attributs['services_id'])) {
                $service = $this->serviceRepositoryEloquent->findByUuid($attributs['services_id'])->first();
                $attributs['services_id'] = $service->id;
            }
            unset($attributs['departments']);
            unset($attributs['services']);

            $item = $this->employerRepositoryEloquent->update($attributs, $employer->id);

            return $item;
        });

        $item = $item->fresh();
        return new EmployerResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmployerDeleteRequest $request, $uuid)
    {
        $employer = $this->employerRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->employerRepositoryEloquent->delete($employer->id);

        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }
}
