<?php

namespace Modules\Administration\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepositoryEloquent;
use Modules\Administration\Http\Resources\PaysResource;
use Modules\Administration\Http\Resources\PayssResource;
use Modules\Administration\Http\Requests\PaysIndexRequest;
use Modules\Administration\Http\Requests\PaysStoreRequest;
use Modules\Administration\Http\Requests\PaysDeleteRequest;
use Modules\Administration\Http\Requests\PaysUpdateRequest;
use Modules\Administration\Http\Controllers\AdministrationController;
use Modules\Administration\Repositories\PaysRepositoryEloquent;


class PaysController extends AdministrationController
{

    /**
     * @var PostRepository
     */
    protected $paysRepositoryEloquent,
        $userRepositoryEloquent;

    public function __construct(PaysRepositoryEloquent $paysRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent)
    {
        parent::__construct();
        $this->paysRepositoryEloquent = $paysRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(PaysIndexRequest $request)
    {

        $donnees = $this->paysRepositoryEloquent->paginate($this->nombrePage);
       
        return new PayssResource($donnees);
    }

    /**
     * Show a resource.
     *
     * @return Response
     */
    public function show(PaysIndexRequest $request, $id)
    {
        $item = $this->paysRepositoryEloquent->findOrFail($id); // Replace `findByUuidOrFail` with `findOrFail`
        return new PaysResource($item);
    }

    /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(PaysStoreRequest $request)
    {

        $attributs = $request->all();
        $item = DB::transaction(function () use ($attributs) {
            // $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
            // $attributs['users_id'] = $user->id;

            $item = $this->paysRepositoryEloquent->create($attributs);

            return $item;
        });
        // $pays->nom = $request->input('nom');
        $item = $item->fresh();

        return new PaysResource($item);
    }

    /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(PaysUpdateRequest $request, $id)
    {
        $item = $this->paysRepositoryEloquent->findOrFail($id); // Remplacez `findByUuidOrFail` par `findOrFail`

        $attributs = $request->all();

        $this->paysRepositoryEloquent->update($attributs, $id); // Utilisez l'`id` pour mettre à jour l'enregistrement directement dans le référentiel.

        $item = $item->fresh();
        return new PaysResource($item);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaysDeleteRequest $request, $id)
    {
        $pays = $this->paysRepositoryEloquent->findOrFail($id); // Remplacez `findByUuidOrFail` par `findOrFail`

        // @TODO : Implémenter les conditions de suppression

        $this->paysRepositoryEloquent->delete($pays->id);

        $data = [
            "message" => __("Item supprimé avec succès"),
        ];

        return reponse_json_transform($data);
    }
}