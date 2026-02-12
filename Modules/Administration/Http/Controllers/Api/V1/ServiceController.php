<?php

namespace Modules\Administration\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Administration\Http\Resources\ServiceResource;
use Modules\Administration\Http\Resources\ServicesResource;
use Modules\Administration\Http\Requests\ServiceIndexRequest;
use Modules\Administration\Http\Requests\ServiceStoreRequest;
use Modules\Administration\Http\Requests\ServiceDeleteRequest;
use Modules\Administration\Http\Requests\ServiceUpdateRequest;
use Modules\Administration\Http\Controllers\AdministrationController;
use Modules\Administration\Repositories\ServiceRepositoryEloquent;
use Modules\Administration\Repositories\DepartmentRepositoryEloquent;


class ServiceController extends AdministrationController
{

    /**
     * @var PostRepository
     */
    protected $serviceRepositoryEloquent,
        $departmentRepositoryEloquent;

    public function __construct(ServiceRepositoryEloquent $serviceRepositoryEloquent, DepartmentRepositoryEloquent $departmentRepositoryEloquent)
    {
        parent::__construct();
        $this->serviceRepositoryEloquent = $serviceRepositoryEloquent;
        $this->departmentRepositoryEloquent = $departmentRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(ServiceIndexRequest $request)
    {
        $donnees = $this->serviceRepositoryEloquent->paginate($this->nombrePage);
        return new ServicesResource($donnees);
    }

    /**
     * Show a resource.
     *
     * @return Response
     */
    public function show(ServiceIndexRequest $request, $uuid)
    {
        $item = $this->serviceRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new ServiceResource($item);
    }

    /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(ServiceStoreRequest $request)
    {
       
        $attributs = $request->all();
        $item = DB::transaction(function () use ($attributs) {
            $departement = $this->departmentRepositoryEloquent->findByUuid($attributs['departments_id'])->first();
            $attributs['departments_id'] = $departement->id;

            $item = $this->serviceRepositoryEloquent->create($attributs);

            return $item;
        });

        $item = $item->fresh();

        return new ServiceResource($item);
    }

    /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(ServiceUpdateRequest $request, $uuid)
    {
        $item = $this->serviceRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        $departement = $this->departmentRepositoryEloquent->findByUuid($attributs['departments_id'])->first();
        $attributs['departments_id'] = $departement->id;



        $item = $this->serviceRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();
        return new ServiceResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(ServiceDeleteRequest $request, $uuid)
    {
        $service = $this->serviceRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->serviceRepositoryEloquent->delete($service->id);

        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }
}
