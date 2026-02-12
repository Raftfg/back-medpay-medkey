<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Illuminate\Http\Response;
use App\Repositories\UserRepositoryEloquent;
use Modules\Stock\Http\Resources\ProductResource;
use Modules\Stock\Http\Resources\ProductsResource;
use Modules\Stock\Http\Requests\ProductIndexRequest;
use Modules\Stock\Http\Requests\ProductStoreRequest;
use Modules\Stock\Http\Requests\ProductDeleteRequest;
use Modules\Stock\Http\Requests\ProductUpdateRequest;
use Modules\Stock\Http\Controllers\StockController;
use Modules\Stock\Repositories\ProductRepositoryEloquent;

class ProductController extends StockController {

    /**
     * @var ProductRepositoryEloquent
     */
    protected $productRepositoryEloquent;

    /**
     * @var UserRepositoryEloquent
     */
    protected $userRepositoryEloquent;

    public function __construct(ProductRepositoryEloquent $productRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent) {
        parent::__construct();
        $this->productRepositoryEloquent = $productRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }
    
    /**
     * Return a listing of the resource.
     * @param ProductIndexRequest $request
     * @return ProductsResource
     */
    public function index(ProductIndexRequest $request)
    {
        $donnees = $this->productRepositoryEloquent->paginate($this->nombrePage);
        return new ProductsResource($donnees);
    }

    /**
     * Show the specified resource.
     * @param ProductIndexRequest $request
     * @param string $uuid
     * @return ProductResource
     */ 
    public function show(ProductIndexRequest $request, $uuid) {
        try {
            $item = $this->productRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return response()->json(['message' => 'Produit non trouvé'], 404);
            }
            return new ProductResource($item);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param ProductStoreRequest $request
     * @return ProductResource
     */
    public function store(ProductStoreRequest $request)
    {
        $attributs = $request->all();

        //This block must be uncommented when the user model will be created and binded
        // $item = DB::transaction(function () use ($attributs) {
        //     $user = $this->userRepositoryEloquent->findByUuid($attributs['user_id'])->first();
        //     $attributs['user_id'] = $user->id;

        //     $item = $this->productRepositoryEloquent->create($attributs);
        //     return $item;
        // });

        //This line must be deleted when the user model will be created and binded
        $attributs['user_id'] = 1;

        $item = $this->productRepositoryEloquent->create($attributs);
        $item = $item->fresh();

        return new ProductResource($item);
    }
    
    /**
     * Update the specified resource in storage.
     * @param ProductUpdateRequest $request
     * @return ProductResource
     */
    public function update(ProductUpdateRequest $request, $uuid)
    {
        $item = $this->productRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        // $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
        // $attributs['users_id'] = $user->id;

        $item = $this->productRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();

        return new ProductResource($item);
    }

    /**
     * Remove the specified resource from storage.
     * @param ProductDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductDeleteRequest $request, $uuid)
    {
        $product = $this->productRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->productRepositoryEloquent->delete($product->id);
        
        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }    
}
