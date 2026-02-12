<?php

namespace Modules\Administration\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepositoryEloquent;
use Modules\Administration\Http\Resources\ProductTypeResource;
use Modules\Administration\Http\Resources\ProductTypesResource;
use Modules\Administration\Http\Requests\ProductTypeIndexRequest;
use Modules\Administration\Http\Requests\ProductTypeStoreRequest;
use Modules\Administration\Http\Requests\ProductTypeDeleteRequest;
use Modules\Administration\Http\Requests\ProductTypeUpdateRequest;
use Modules\Administration\Http\Controllers\AdministrationController;
use Modules\Administration\Repositories\ProductTypeRepositoryEloquent;


class ProductTypeController extends AdministrationController
{

    /**
     * @var PostRepository
     */
    protected $productTypeRepositoryEloquent,
        $userRepositoryEloquent;

    public function __construct(ProductTypeRepositoryEloquent $productTypeRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent)
    {
        parent::__construct();
        $this->productTypeRepositoryEloquent = $productTypeRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(ProductTypeIndexRequest $request)
    {
        $donnees = $this->productTypeRepositoryEloquent->paginate($this->nombrePage);
        return new ProductTypesResource($donnees);
    }

    /**
     * Show a resource.
     *
     * @return Response
     */
    public function show(ProductTypeIndexRequest $request, $uuid)
    {
        $item = $this->productTypeRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new ProductTypeResource($item);
    }

    /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(ProductTypeStoreRequest $request)
    {

        $attributs = $request->all();
        $item = DB::transaction(function () use ($attributs) {
            $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
            $attributs['users_id'] = $user->id;

            $item = $this->productTypeRepositoryEloquent->create($attributs);

            return $item;
        });

        $item = $item->fresh();

        return new ProductTypeResource($item);
    }

    /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(ProductTypeUpdateRequest $request, $uuid)
    {
        $item = $this->productTypeRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
        $attributs['users_id'] = $user->id;



        $item = $this->productTypeRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();
        return new ProductTypeResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductTypeDeleteRequest $request, $uuid)
    {
        $productType = $this->productTypeRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->productTypeRepositoryEloquent->delete($productType->id);

        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }
}
