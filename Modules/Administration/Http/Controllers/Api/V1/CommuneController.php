<?php

namespace Modules\Administration\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepositoryEloquent;
use Modules\Administration\Http\Resources\CommuneResource;
use Modules\Administration\Http\Resources\CommunesResource;
use Modules\Administration\Http\Requests\CommuneIndexRequest;
use Modules\Administration\Http\Requests\CommuneStoreRequest;
use Modules\Administration\Http\Requests\CommuneDeleteRequest;
use Modules\Administration\Http\Requests\CommuneUpdateRequest;
use Modules\Administration\Http\Controllers\AdministrationController;
use Modules\Administration\Repositories\CommuneRepositoryEloquent;
use Modules\Administration\Entities\Commune;


class CommuneController extends AdministrationController
{

    /**
     * @var PostRepository
     */
    protected $communeRepositoryEloquent,
        $userRepositoryEloquent;

    public function __construct(CommuneRepositoryEloquent $communeRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent)
    {
        parent::__construct();
        $this->communeRepositoryEloquent = $communeRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(CommuneIndexRequest $request)
    {
        $donnees = $this->communeRepositoryEloquent->paginate($this->nombrePage);
        return new CommunesResource($donnees);
    }

    /**
     * Show a resource.
     *
     * @return Response
     */
    public function show(CommuneIndexRequest $request, $id)
    {
        $item = $this->communeRepositoryEloquent->findOrFail($id); // Replace `findByUuidOrFail` with `findOrFail`
        return new CommuneResource($item);
    }

    /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(CommuneStoreRequest $request)
    {

        $attributs = $request->all();
        $item = DB::transaction(function () use ($attributs) {
            // $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
            // $attributs['users_id'] = $user->id;

            $item = $this->communeRepositoryEloquent->create($attributs);

            return $item;
        });
        // $commune->nom = $request->input('nom');
        $item = $item->fresh();

        return new CommuneResource($item);
    }

    /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(CommuneUpdateRequest $request, $id)
    {
        $item = $this->communeRepositoryEloquent->findOrFail($id); // Remplacez `findByUuidOrFail` par `findOrFail`

        $attributs = $request->all();

        $this->communeRepositoryEloquent->update($attributs, $id); // Utilisez l'`id` pour mettre à jour l'enregistrement directement dans le référentiel.

        $item = $item->fresh();
        return new CommuneResource($item);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(CommuneDeleteRequest $request, $id)
    {
        $commune = $this->communeRepositoryEloquent->findOrFail($id); // Remplacez `findByUuidOrFail` par `findOrFail`

        // @TODO : Implémenter les conditions de suppression

        $this->communeRepositoryEloquent->delete($commune->id);

        $data = [
            "message" => __("Item supprimé avec succès"),
        ];

        return reponse_json_transform($data);
    }

    public function getCommunesByDepartement(Request $request)
    {
        $departementId = $request->input('departementId');

        $communes = Commune::where('departements_id', $departementId)->get();

        return response()->json($communes);
    }
}
