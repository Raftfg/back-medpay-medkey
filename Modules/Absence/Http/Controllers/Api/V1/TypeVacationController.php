<?php

namespace Modules\Absence\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Absence\Http\Controllers\AbsenceController;
use Modules\Absence\Http\Resources\TypeVacationResource;
use Modules\Absence\Http\Resources\TypeVacationsResource;
use Modules\Absence\Http\Requests\TypeVacationIndexRequest;
use Modules\Absence\Http\Requests\TypeVacationStoreRequest;
use Modules\Absence\Http\Requests\TypeVacationDeleteRequest;
use Modules\Absence\Http\Requests\TypeVacationUpdateRequest;
use Modules\Absence\Repositories\TypeVacationRepositoryEloquent;

class TypeVacationController extends AbsenceController
{

    /**
     * @var PostRepository
     */
    protected $typeVacationRepositoryEloquent;

    public function __construct(TypeVacationRepositoryEloquent $typeVacationRepositoryEloquent)
    {
        parent::__construct();
        $this->typeVacationRepositoryEloquent = $typeVacationRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function show(TypeVacationIndexRequest $request, $uuid)
    {
        $item = $this->typeVacationRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new TypeVacationResource($item);
    }

    public function index(TypeVacationIndexRequest $request)
    {
        $donnees = $this->typeVacationRepositoryEloquent->paginate($this->nombrePage);
        return new TypeVacationsResource($donnees);
    }

    /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(TypeVacationStoreRequest $request)
    {
        $item = $this->typeVacationRepositoryEloquent->create($request->all());
        $item = $item->fresh();
        return new TypeVacationResource($item);
    }

    /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(TypeVacationUpdateRequest $request, $uuid)
    {
        $item = $this->typeVacationRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();
        $item = $this->typeVacationRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();
        return new TypeVacationResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(TypeVacationDeleteRequest $request, $uuid)
    {
        $typeVacation = $this->typeVacationRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->typeVacationRepositoryEloquent->delete($typeVacation->id);

        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }
}
