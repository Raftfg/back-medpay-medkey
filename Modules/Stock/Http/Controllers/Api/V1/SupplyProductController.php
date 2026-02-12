<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepositoryEloquent;
use Modules\Stock\Http\Controllers\Api\V1\StockProductController;
use Modules\Stock\Http\Resources\SupplyProductResource;
use Modules\Stock\Http\Resources\SupplyProductsResource;
use Modules\Stock\Http\Requests\SupplyProductIndexRequest;
use Modules\Stock\Http\Requests\SupplyProductStoreRequest;
use Modules\Stock\Http\Requests\StockProductStoreRequest;
use Modules\Stock\Http\Requests\SupplyProductDeleteRequest;
use Modules\Stock\Http\Requests\SupplyProductUpdateRequest;
use Modules\Stock\Http\Controllers\StockController;
use Modules\Stock\Repositories\SupplyProductRepositoryEloquent;
use Modules\Stock\Repositories\SupplyRepositoryEloquent;
use Modules\Stock\Repositories\StockProductRepositoryEloquent;
use Modules\Stock\Repositories\StockRepositoryEloquent;
use Modules\Stock\Repositories\ProductRepositoryEloquent;
use Modules\Stock\Repositories\SupplierRepositoryEloquent;

class SupplyProductController extends StockController {

    /**
     * @var SupplyProductRepositoryEloquent
     */
    protected $supplyProductRepositoryEloquent;

    /**
     * @var SupplierRepositoryEloquent
     */
    protected $supplierRepositoryEloquent;

    /**
     * @var UserRepositoryEloquent
     */
    protected $userRepositoryEloquent;

    /**
     * @var SupplyRepositoryEloquent
     */
    protected $supplyRepositoryEloquent;

    /**
     * @var ProductRepositoryEloquent
     */
    protected $productRepositoryEloquent;

    /**
     * @var StockProductRepositoryEloquent
     */
    protected $stockProductRepositoryEloquent;

    /**
     * @var StockRepositoryEloquent
     */
    protected $stockRepositoryEloquent;

    public function __construct(SupplyProductRepositoryEloquent $supplyProductRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent, SupplyRepositoryEloquent $supplyRepositoryEloquent,
    ProductRepositoryEloquent $productRepositoryEloquent, StockProductRepositoryEloquent $stockProductRepositoryEloquent, 
    StockRepositoryEloquent $stockRepositoryEloquent,
    SupplierRepositoryEloquent $supplierRepositoryEloquent) {
        parent::__construct();
        $this->supplyProductRepositoryEloquent = $supplyProductRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
        $this->supplyRepositoryEloquent = $supplyRepositoryEloquent;
        $this->productRepositoryEloquent = $productRepositoryEloquent;
        $this->stockProductRepositoryEloquent = $stockProductRepositoryEloquent;
        $this->stockRepositoryEloquent = $stockRepositoryEloquent;
        $this->supplierRepositoryEloquent = $supplierRepositoryEloquent;
    }
    
      /**
     * Return a listing of the resource.
     * @param SupplyProductIndexRequest $request
     * @return SupplyProductsResource
     */
    public function index(SupplyProductIndexRequest $request)
    {
        $donnees = $this->supplyProductRepositoryEloquent->paginate($this->nombrePage);   
        return new SupplyProductsResource($donnees);
    }

    /**
     * Show the specified resource.
     * @param SupplyProductIndexRequest $request
     * @param string $uuid
     * @return SupplyProductResource
     */ 
    public function show(SupplyProductIndexRequest $request, $uuid) {
        try {
            $item = $this->supplyProductRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return response()->json(['message' => 'Produit d\'approvisionnement non trouvé'], 404);
            }
            return new SupplyProductResource($item);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param SupplyProductStoreRequest $request
     * @return SupplyProductResource
     */
    public function store(SupplyProductStoreRequest $request)
    {
        $attributes['lot_number'] = $request->lot_number;
        $attributes['units_per_box'] = $request->units_per_box;
        $attributes['expire_date'] = $request->expire_date;
        $attributes['quantity'] = $request->quantity;
        $attributes['purchase_price'] = $request->purchase_price;
        $attributes['profit_margin'] = $request->profit_margin;
        $attributes['supply_id'] = $request->supply_id;
        $attributes['product_id'] = $request->product_id;
        $attributes['supplier_id'] = $request->supplier_id;

        $supplyProduct = DB::transaction(function () use ($attributes) {
            $supply = $this->supplyRepositoryEloquent->find($attributes['supply_id'])->first();
            $supplier = $this->supplierRepositoryEloquent->find($attributes['supplier_id'])->first();

            if (is_null($attributes['profit_margin'])) {
                $attributes['profit_margin'] = $supplier->profit_margin;
            }
            //create the supply product
            $supplyProduct = $this->supplyProductRepositoryEloquent->create($attributes);

            // Reflect the done supply on the stock
            $stockProductController = new StockProductController(
                $this->stockProductRepositoryEloquent, 
                $this->userRepositoryEloquent, 
                $this->stockRepositoryEloquent,
                $this->productRepositoryEloquent);

            $purchasePrice = $supplyProduct->purchase_price;
            $profitMargin = $supplyProduct->profit_margin;
            $unitPerBox =  $supplyProduct->units_per_box;
            $quantity =  $supplyProduct->quantity;

            // The quantity equal at the number of box * the unit per box because they sell the product per unit of box
            $quantity =  $quantity * $unitPerBox;
            // This is the selling price of a single unit of box
            $sellingPrice = ($purchasePrice + ($profitMargin * $purchasePrice / 100)) ;

            $stockProductRequest = new StockProductStoreRequest();
            $stockProductRequest->lot_number = $supplyProduct->lot_number;
            $stockProductRequest->units_per_box = $supplyProduct->units_per_box;
            $stockProductRequest->expire_date = $supplyProduct->expire_date;
            $stockProductRequest->quantity = $quantity;
            $stockProductRequest->purchase_price = $supplyProduct->purchase_price;
            $stockProductRequest->selling_price = $sellingPrice;
            $stockProductRequest->stock_id = $supply->stock_id;
            $stockProductRequest->product_id = $supplyProduct->product_id;

            $stockProduct = $stockProductController->store($stockProductRequest);
            return $supplyProduct;
        });

        $supplyProduct = $supplyProduct->fresh();
        return new SupplyProductResource($supplyProduct);
    }
    
    /**
     * Update the specified resource in storage.
     * @param SupplyProductUpdateRequest $request
     * @return SupplyProductResource
     */
    public function update(SupplyProductUpdateRequest $request, $uuid)
    {
        $supplyProduct = $this->supplyProductRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        $supplyProduct = $this->supplyProductRepositoryEloquent->update($attributs, $supplyProduct->id);
        $supplyProduct = $supplyProduct->fresh();

        return new SupplyProductResource($supplyProduct);
    }

    /**
     * Remove the specified resource from storage.
     * @param SupplyProductDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(SupplyProductDeleteRequest $request, $uuid)
    {
        $supplyProduct = $this->supplyProductRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        
        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }    
}
