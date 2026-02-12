<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Illuminate\Http\Response;
use App\Http\Requests\UuidValidateRequest;
use App\Repositories\UserRepositoryEloquent;
use Modules\Stock\Http\Resources\TypeProductResource;
use Modules\Stock\Http\Resources\TypeProductsResource;
use Modules\Stock\Http\Requests\TypeProductIndexRequest;
use Modules\Stock\Http\Requests\TypeProductStoreRequest;
use Modules\Stock\Http\Requests\TypeProductDeleteRequest;
use Modules\Stock\Http\Requests\TypeProductUpdateRequest;
use Modules\Stock\Http\Controllers\StockController;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Repositories\TypeProductRepositoryEloquent;

class TypeProductController extends StockController {

    /**
     * @var TypeProductRepositoryEloquent
     */
    protected $typeProductRepositoryEloquent;

    /**
     * @var UserRepositoryEloquent
     */
    protected $userRepositoryEloquent;

    public function __construct(TypeProductRepositoryEloquent $typeProductRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent) {
        parent::__construct();
        $this->typeProductRepositoryEloquent = $typeProductRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }
    
    /**
     * Return a listing of the resource.
     * @param TypeProductIndexRequest $request
     * @return TypeProductsResource
     */
    public function index(TypeProductIndexRequest $request)
    {
        $donnees = $this->typeProductRepositoryEloquent->paginate($this->nombrePage);
        return new TypeProductsResource($donnees);
    }


    /**
     * Show the specified resource.
     * @param TypeProductIndexRequest $request
     * @param string $uuid
     * @return TypeProductResource
     */
    public function show(TypeProductIndexRequest $request, $uuid) {
        $item = $this->typeProductRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new TypeProductResource($item);
    }
    
    /**
     * Store a newly created resource in storage.
     * @param TypeProductStoreRequest $request
     * @return TypeProductResource
     */
    public function store(TypeProductStoreRequest $request)
    {
        $attributs = $request->all();

        //This block must be uncommented when the user model will be created and binded
        // $item = DB::transaction(function () use ($attributs) {
        //     $user = $this->userRepositoryEloquent->findByUuid($attributs['user_id'])->first();
        //     $attributs['user_id'] = $user->id;

        //     $item = $this->typeProductRepositoryEloquent->create($attributs);
        //     return $item;
        // });

        //This line must be deleted when the user model will be created and binded
        $attributs['user_id'] = 1;

        // $item = $this->typeProductRepositoryEloquent->create($attributs);
        // $item = $item->fresh();

        return new TypeProductResource($item);
    }
    
    /**
     * Update the specified resource in storage.
     * @param TypeProductUpdateRequest $request
     * @return TypeProductResource
     */
    public function update(TypeProductUpdateRequest $request, $uuid)
    {
        $item = $this->typeProductRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        // $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
        // $attributs['users_id'] = $user->id;

        // $item = $this->typeProductRepositoryEloquent->update($attributs, $item->id);
        // $item = $item->fresh();

        return new TypeProductResource($item);
    }

    /**
     * Remove the specified resource from storage.
     * @param TypeProductDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(TypeProductDeleteRequest $request, $uuid)
    {
        $typeProduct = $this->typeProductRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->typeProductRepositoryEloquent->delete($typeProduct->id);
        
        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }    

    public function getCategories($uuid)
    {
        $typeProduct = $this->typeProductRepositoryEloquent->findByUuid($uuid)->first();
        $typeProductId = $typeProduct->id;

        $categories = DB::table('categories')
        ->where('type_product_id', $typeProductId)
        ->get();

        return reponse_json_transform($categories);
    }
}
