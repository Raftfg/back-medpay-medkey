<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\DB;
use Modules\Stock\Entities\StockProduct;
use App\Repositories\UserRepositoryEloquent;
use Modules\Stock\Http\Controllers\StockController;
use Modules\Stock\Repositories\StockRepositoryEloquent;
use Modules\Stock\Http\Requests\StockProductStoreRequest;
use Modules\Stock\Repositories\ProductRepositoryEloquent;
use Modules\Stock\Http\Resources\StockTransferProductResource;
use Modules\Stock\Repositories\StockProductRepositoryEloquent;
use Modules\Stock\Http\Resources\StockTransferProductsResource;
use Modules\Stock\Repositories\StockTransferRepositoryEloquent;
use Modules\Stock\Http\Controllers\Api\V1\StockProductController;
use Modules\Stock\Http\Requests\StockTransferProductIndexRequest;
use Modules\Stock\Http\Requests\StockTransferProductStoreRequest;
use Modules\Stock\Http\Requests\StockTransferProductDeleteRequest;
use Modules\Stock\Http\Requests\StockTransferProductUpdateRequest;
use Modules\Stock\Repositories\StockTransferProductRepositoryEloquent;

class StockTransferProductContoller extends StockController {

    /**
     * @var StockTransferProductRepositoryEloquent
     */
    protected $stockTransferProductRepositoryEloquent;

    /**
     * @var StockTransferRepositoryEloquent
     */
    protected $stockTransferRepositoryEloquent;

    /**
     * @var StockProductRepositoryEloquent
     */
    protected $stockProductRepositoryEloquent;

    /**
     * @var UserRepositoryEloquent
     */
    protected $userRepositoryEloquent;

    /**
     * @var StockRepositoryEloquent
     */
    protected $stockRepositoryEloquent;

    /**
     * @var ProductRepositoryEloquent
     */
    protected $productRepositoryEloquent;


    public function __construct(
        StockTransferProductRepositoryEloquent $stockTransferProductRepositoryEloquent, 
        StockTransferRepositoryEloquent $stockTransferRepositoryEloquent, 
        StockProductRepositoryEloquent $stockProductRepositoryEloquent,
        UserRepositoryEloquent $userRepositoryEloquent,
        StockRepositoryEloquent $stockRepositoryEloquent,
        ProductRepositoryEloquent $productRepositoryEloquent
    ) {
        parent::__construct();
        $this->stockTransferProductRepositoryEloquent = $stockTransferProductRepositoryEloquent;
        $this->stockTransferRepositoryEloquent = $stockTransferRepositoryEloquent;
        $this->stockProductRepositoryEloquent = $stockProductRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
        $this->stockRepositoryEloquent = $stockRepositoryEloquent;
        $this->productRepositoryEloquent = $productRepositoryEloquent;
    }
    
      /**
     * Return a listing of the resource.
     * @param StockTransferProductIndexRequest $request
     * @return StockTransferProductsResource
     */
    public function index(StockTransferProductIndexRequest $request)
    {
        $donnees = $this->stockTransferProductRepositoryEloquent->paginate($this->nombrePage);   
        return new StockTransferProductsResource($donnees);
    }

    /**
     * Show the specified resource.
     * @param StockTransferProductIndexRequest $request
     * @param string $uuid
     * @return StockTransferProductResource
     */ 
    public function show(StockTransferProductIndexRequest $request, $uuid) {
        try {
            $item = $this->stockTransferProductRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return response()->json(['message' => 'Produit transféré non trouvé'], 404);
            }
            return new StockTransferProductResource($item);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param StockTransferProductStoreRequest $request
     * @return StockTransferProductResource
     */
    public function store(StockTransferProductStoreRequest $request)
    {
        $attributes['quantity_transfered'] = $request->quantity_transfered;
        $attributes['stock_transfer_id'] = $request->stock_transfer_id;
        $attributes['stock_product_id'] = $request->stock_product_id;

        // If the line does not exist, create a new one
        $stockTransferProduct = DB::transaction(function () use ($attributes) {
            $stockTransfer = $this->stockTransferRepositoryEloquent
                ->find($attributes['stock_transfer_id'])
                ->first();
                
            $existingStockProduct =  StockProduct::where('id', $attributes['stock_product_id'])->first();

            $quantityToTransfer = $attributes['quantity_transfered'];
            $quantityInStock = $existingStockProduct->quantity;

            $this->checkQuantityInStock($quantityToTransfer, $quantityInStock);

            $newQuantity = $quantityInStock - $quantityToTransfer;

            $stockProductAttributes['quantity'] = $newQuantity;
            $existingStockProduct = $this->stockProductRepositoryEloquent
                ->update($stockProductAttributes, $existingStockProduct->id);

            // If the transfer is going to a stock, then adjust the stock
            if ($stockTransfer->model_name == 'Stock') {
                $stockProductController = new StockProductController(
                    $this->stockProductRepositoryEloquent, 
                    $this->userRepositoryEloquent, 
                    $this->stockRepositoryEloquent,
                    $this->productRepositoryEloquent);
                
                $stockProductRequest = new StockProductStoreRequest();
                $stockProductRequest->lot_number = $existingStockProduct->lot_number;
                $stockProductRequest->units_per_box = $existingStockProduct->units_per_box;
                $stockProductRequest->expire_date = $existingStockProduct->expire_date;
                $stockProductRequest->quantity = $quantityToTransfer;
                $stockProductRequest->purchase_price = $existingStockProduct->purchase_price;
                $stockProductRequest->selling_price = $existingStockProduct->selling_price;
                $stockProductRequest->stock_id = $stockTransfer->model_id;
                $stockProductRequest->product_id = $existingStockProduct->product_id;

                $newStockProduct = $stockProductController->store($stockProductRequest);
            }

            $stockTransferProduct = $this->stockTransferProductRepositoryEloquent->create($attributes);
            return $stockTransferProduct;
        });

        $stockTransferProduct = $stockTransferProduct->fresh();
        return new StockTransferProductResource($stockTransferProduct);
    }
    
    /**
     * Update the specified resource in storage.
     * @param StockTransferProductUpdateRequest $request
     * @return StockTransferProductResource
     */
    public function update(StockTransferProductUpdateRequest $request, $uuid)
    {
        $stockTransferProduct = $this->stockTransferProductRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        $stockTransferProduct = $this->stockTransferProductRepositoryEloquent->update($attributs, $stockTransferProduct->id);
        $stockTransferProduct = $stockTransferProduct->fresh();

        return new StockTransferProductResource($stockTransferProduct);
    }

    /**
     * Remove the specified resource from storage.
     * @param StockTransferProductDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(StockTransferProductDeleteRequest $request, $uuid)
    {
        $stockTransferProduct = $this->stockTransferProductRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        
        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }  
    
    // Check if the quantity to destock is above the quantity in stock
    private function checkQuantityInStock($quantityToRetrieve, $quantityInStock)
    {
        if ($quantityToRetrieve > $quantityInStock) {
            throw new \RuntimeException('La quantité à transférer excède la quantité en stock');
        }
    }
}
