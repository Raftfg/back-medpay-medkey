<?php

namespace Modules\Administration\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepositoryEloquent;
use Modules\Administration\Entities\Quartier;
use Modules\Administration\Http\Resources\QuartierResource;
use Modules\Administration\Http\Resources\QuartiersResource;
use Modules\Administration\Http\Requests\QuartierIndexRequest;
use Modules\Administration\Http\Requests\QuartierStoreRequest;
use Modules\Administration\Http\Requests\QuartierDeleteRequest;
use Modules\Administration\Http\Requests\QuartierUpdateRequest;
use Modules\Administration\Repositories\QuartierRepositoryEloquent;
use Modules\Administration\Http\Controllers\AdministrationController;


class QuartierController extends AdministrationController
{

    /**
     * @var PostRepository
     */
    protected $quartierRepositoryEloquent,
        $userRepositoryEloquent;

    public function __construct(QuartierRepositoryEloquent $quartierRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent)
    {
        parent::__construct();
        $this->quartierRepositoryEloquent = $quartierRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(QuartierIndexRequest $request)
    {
        $donnees = $this->quartierRepositoryEloquent->paginate($this->nombrePage);
        return new QuartiersResource($donnees);
    }

    /**
     * Show a resource.
     *
     * @return Response
     */
    public function show(QuartierIndexRequest $request, $id)
    {
        $item = $this->quartierRepositoryEloquent->findOrFail($id); // Replace `findByUuidOrFail` with `findOrFail`
        return new QuartierResource($item);
    }

    /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(QuartierStoreRequest $request)
    {

        $attributs = $request->all();
        $item = DB::transaction(function () use ($attributs) {
            // $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
            // $attributs['users_id'] = $user->id;

            $item = $this->quartierRepositoryEloquent->create($attributs);

            return $item;
        });
        // $quartier->nom = $request->input('nom');
        $item = $item->fresh();

        return new QuartierResource($item);
    }

    /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(QuartierUpdateRequest $request, $id)
    {
        $item = $this->quartierRepositoryEloquent->findOrFail($id); // Remplacez `findByUuidOrFail` par `findOrFail`

        $attributs = $request->all();

        $this->quartierRepositoryEloquent->update($attributs, $id); // Utilisez l'`id` pour mettre à jour l'enregistrement directement dans le référentiel.

        $item = $item->fresh();
        return new QuartierResource($item);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(QuartierDeleteRequest $request, $id)
    {
        $quartier = $this->quartierRepositoryEloquent->findOrFail($id); // Remplacez `findByUuidOrFail` par `findOrFail`

        // @TODO : Implémenter les conditions de suppression

        $this->quartierRepositoryEloquent->delete($quartier->id);

        $data = [
            "message" => __("Item supprimé avec succès"),
        ];

        return reponse_json_transform($data);
    }

    public function getQuartiersByArrondissement(Request $request)
    {
        $arrondissementId = $request->input('arrondissementId');

        $quartiers = Quartier::where('arrondissements_id', $arrondissementId)->get();

        return response()->json($quartiers);
    }
}
