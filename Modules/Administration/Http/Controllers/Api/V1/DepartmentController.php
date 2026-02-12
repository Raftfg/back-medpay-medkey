<?php

namespace Modules\Administration\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
// use App\Repositories\UserRepositoryEloquent;
use Modules\Administration\Http\Resources\DepartmentResource;
use Modules\Administration\Http\Resources\DepartmentsResource;
use Modules\Administration\Http\Requests\DepartmentIndexRequest;
use Modules\Administration\Http\Requests\DepartmentStoreRequest;
use Modules\Administration\Http\Requests\DepartmentDeleteRequest;
use Modules\Administration\Http\Requests\DepartmentUpdateRequest;
use Modules\Administration\Http\Controllers\AdministrationController;
use Modules\Administration\Repositories\DepartmentRepositoryEloquent;


class DepartmentController extends AdministrationController
{

    /**
     * @var PostRepository
     */
    protected $departmentRepositoryEloquent;
       

    public function __construct(DepartmentRepositoryEloquent $departmentRepositoryEloquent)
    {
        parent::__construct();
        $this->departmentRepositoryEloquent = $departmentRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(DepartmentIndexRequest $request)
    {
        $donnees = $this->departmentRepositoryEloquent->paginate($this->nombrePage);
        return new DepartmentsResource($donnees);
    }

    /**
     * Show a resource.
     *
     * @return Response
     */
    public function show(DepartmentIndexRequest $request, $uuid)
    {
        $item = $this->departmentRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new DepartmentResource($item);
    }

    /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(DepartmentStoreRequest $request)
    {

        $attributs = $request->all();
        $item = DB::transaction(function () use ($attributs) {

            $item = $this->departmentRepositoryEloquent->create($attributs);

            return $item;
        });

        $item = $item->fresh();

        return new DepartmentResource($item);
    }

    /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(DepartmentUpdateRequest $request, $uuid)
    {
        $item = $this->departmentRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        $item = $this->departmentRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();
        return new DepartmentResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(DepartmentDeleteRequest $request, $uuid)
    {
        $department = $this->departmentRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->departmentRepositoryEloquent->delete($department->id);

        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }
}
