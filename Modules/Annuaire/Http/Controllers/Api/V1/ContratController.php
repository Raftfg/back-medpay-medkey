<?php

namespace Modules\Annuaire\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Annuaire\Http\Resources\ContratResource;
use Modules\Annuaire\Http\Resources\ContratsResource;
use Modules\Annuaire\Http\Requests\ContratIndexRequest;
use Modules\Annuaire\Http\Requests\ContratStoreRequest;
use Modules\Annuaire\Http\Requests\ContratDeleteRequest;
use Modules\Annuaire\Http\Requests\ContratUpdateRequest;
use Modules\Annuaire\Http\Controllers\AnnuaireController;
use Modules\Annuaire\Repositories\ContratRepositoryEloquent;
use Modules\Annuaire\Repositories\EmployerRepositoryEloquent;


class ContratController extends AnnuaireController {

    /**
     * @var PostRepository
     */
    protected $contratRepositoryEloquent, $employerRepositoryEloquent;

    public function __construct(ContratRepositoryEloquent $contratRepositoryEloquent, EmployerRepositoryEloquent $employerRepositoryEloquent) {
        parent::__construct();
        $this->contratRepositoryEloquent = $contratRepositoryEloquent;
        $this->employerRepositoryEloquent = $employerRepositoryEloquent;

    }

   /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(ContratIndexRequest $request)
    {
        $donnees = $this->contratRepositoryEloquent->paginate($this->nombrePage);
        return new ContratsResource($donnees);
    }

    /**
     * Show a resource.
     *
     * @return Response
     */
    public function show(ContratIndexRequest $request, $uuid) {
        $item = $this->contratRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new ContratResource($item);
    }

   /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(ContratStoreRequest $request)
    {
        $attributs = $request->all();

        $item = DB::transaction(function () use ($attributs) {
            $employer = $this->employerRepositoryEloquent->findByUuid($attributs['employee_id'])->first();
            $attributs['employee_id'] = $employer->id;

            $item = $this->contratRepositoryEloquent->create($attributs);

            return $item;
        });

        $item = $item->fresh();

        return new ContratResource($item);
    }

   /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(ContratUpdateRequest $request, $uuid)
    {
        $item = $this->contratRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        $employer = $this->employerRepositoryEloquent->findByUuid($attributs['employee_id'])->first();
        $attributs['employee_id'] = $employer->id;

        $item = $this->contratRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();
        return new ContratResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(ContratDeleteRequest $request, $uuid)
    {
        $contrat = $this->contratRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->contratRepositoryEloquent->delete($contrat->id);

        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }
}
