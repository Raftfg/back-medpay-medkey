<?php

namespace Modules\Administration\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepositoryEloquent;
use Modules\Administration\Http\Resources\DepartementResource;
use Modules\Administration\Http\Resources\DepartementsResource;
use Modules\Administration\Http\Requests\DepartementIndexRequest;
use Modules\Administration\Http\Requests\DepartementStoreRequest;
use Modules\Administration\Http\Requests\DepartementDeleteRequest;
use Modules\Administration\Http\Requests\DepartementUpdateRequest;
use Modules\Administration\Http\Controllers\AdministrationController;
use Modules\Administration\Repositories\DepartementRepositoryEloquent;


class DepartementController extends AdministrationController
{

    /**
     * @var PostRepository
     */
    protected $departementRepositoryEloquent,
        $userRepositoryEloquent;

    public function __construct(DepartementRepositoryEloquent $departementRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent)
    {
        parent::__construct();
        $this->departementRepositoryEloquent = $departementRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(DepartementIndexRequest $request)
    {
        $donnees = $this->departementRepositoryEloquent->paginate($this->nombrePage);
        return new DepartementsResource($donnees);
    }

    /**
     * Show a resource.
     *
     * @return Response
     */
    public function show(DepartementIndexRequest $request, $id)
    {
        $item = $this->departementRepositoryEloquent->findOrFail($id); // Replace `findByUuidOrFail` with `findOrFail`
        return new DepartementResource($item);
    }

    /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(DepartementStoreRequest $request)
    {

        $attributs = $request->all();
        $item = DB::transaction(function () use ($attributs) {
            // $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
            // $attributs['users_id'] = $user->id;

            $item = $this->departementRepositoryEloquent->create($attributs);

            return $item;
        });
        // $departement->nom = $request->input('nom');
        $item = $item->fresh();

        return new DepartementResource($item);
    }

    /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(DepartementUpdateRequest $request, $id)
    {
        $item = $this->departementRepositoryEloquent->findOrFail($id); // Remplacez `findByUuidOrFail` par `findOrFail`

        $attributs = $request->all();

        $this->departementRepositoryEloquent->update($attributs, $id); // Utilisez l'`id` pour mettre à jour l'enregistrement directement dans le référentiel.

        $item = $item->fresh();
        return new DepartementResource($item);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(DepartementDeleteRequest $request, $id)
    {
        $departement = $this->departementRepositoryEloquent->findOrFail($id); // Remplacez `findByUuidOrFail` par `findOrFail`

        // @TODO : Implémenter les conditions de suppression

        $this->departementRepositoryEloquent->delete($departement->id);

        $data = [
            "message" => __("Item supprimé avec succès"),
        ];

        return reponse_json_transform($data);
    }
}
