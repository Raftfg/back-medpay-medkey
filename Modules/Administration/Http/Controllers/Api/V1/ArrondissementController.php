<?php

namespace Modules\Administration\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepositoryEloquent;
use Modules\Administration\Entities\Arrondissement;
use Modules\Administration\Http\Resources\ArrondissementResource;
use Modules\Administration\Http\Resources\ArrondissementsResource;
use Modules\Administration\Http\Requests\ArrondissementIndexRequest;
use Modules\Administration\Http\Requests\ArrondissementStoreRequest;
use Modules\Administration\Http\Controllers\AdministrationController;
use Modules\Administration\Http\Requests\ArrondissementDeleteRequest;
use Modules\Administration\Http\Requests\ArrondissementUpdateRequest;
use Modules\Administration\Repositories\ArrondissementRepositoryEloquent;


class ArrondissementController extends AdministrationController
{

    /**
     * @var PostRepository
     */
    protected $arrondissementRepositoryEloquent,
        $userRepositoryEloquent;

    public function __construct(ArrondissementRepositoryEloquent $arrondissementRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent)
    {
        parent::__construct();
        $this->arrondissementRepositoryEloquent = $arrondissementRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(ArrondissementIndexRequest $request)
    {
        $donnees = $this->arrondissementRepositoryEloquent->paginate($this->nombrePage);
        return new ArrondissementsResource($donnees);
    }

    /**
     * Show a resource.
     *
     * @return Response
     */
    public function show(ArrondissementIndexRequest $request, $id)
    {
        $item = $this->arrondissementRepositoryEloquent->findOrFail($id); // Replace `findByUuidOrFail` with `findOrFail`
        return new ArrondissementResource($item);
    }

    /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(ArrondissementStoreRequest $request)
    {

        $attributs = $request->all();
        $item = DB::transaction(function () use ($attributs) {
            // $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
            // $attributs['users_id'] = $user->id;

            $item = $this->arrondissementRepositoryEloquent->create($attributs);

            return $item;
        });
        // $arrondissement->nom = $request->input('nom');
        $item = $item->fresh();

        return new ArrondissementResource($item);
    }

    /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(ArrondissementUpdateRequest $request, $id)
    {
        $item = $this->arrondissementRepositoryEloquent->findOrFail($id); // Remplacez `findByUuidOrFail` par `findOrFail`

        $attributs = $request->all();

        $this->arrondissementRepositoryEloquent->update($attributs, $id); // Utilisez l'`id` pour mettre à jour l'enregistrement directement dans le référentiel.

        $item = $item->fresh();
        return new ArrondissementResource($item);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(ArrondissementDeleteRequest $request, $id)
    {
        $arrondissement = $this->arrondissementRepositoryEloquent->findOrFail($id); // Remplacez `findByUuidOrFail` par `findOrFail`

        // @TODO : Implémenter les conditions de suppression

        $this->arrondissementRepositoryEloquent->delete($arrondissement->id);

        $data = [
            "message" => __("Item supprimé avec succès"),
        ];

        return reponse_json_transform($data);
    }


    public function getArrondissementsByCommune(Request $request)
    {
        $communeId = $request->input('communeId');

        $arrondissements = Arrondissement::where('communes_id', $communeId)->get();

        return response()->json($arrondissements);
    }
}
