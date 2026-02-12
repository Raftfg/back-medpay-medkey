<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepositoryEloquent;
use Modules\Stock\Entities\Supply;
use Modules\Stock\Entities\SupplyProduct;
use Modules\Stock\Http\Resources\SupplyResource;
use Modules\Stock\Http\Resources\SuppliesResource;
use Modules\Stock\Http\Resources\SupplyProductsResource;
use Modules\Stock\Http\Requests\SupplyIndexRequest;
use Modules\Stock\Http\Requests\SupplyStoreRequest;
use Modules\Stock\Http\Requests\SupplyProductStoreRequest;
use Modules\Stock\Http\Requests\SupplyDeleteRequest;
use Modules\Stock\Http\Requests\SupplyUpdateRequest;
use Modules\Stock\Repositories\SupplyRepositoryEloquent;
use Modules\Stock\Repositories\SupplyProductRepositoryEloquent;
use Modules\Stock\Repositories\StockRepositoryEloquent;
use Modules\Stock\Repositories\ProductRepositoryEloquent;
use Modules\Stock\Repositories\SupplierRepositoryEloquent;
use Modules\Stock\Repositories\StockProductRepositoryEloquent;
use Modules\Stock\Http\Controllers\StockController;
use Modules\Stock\Http\Controllers\Api\V1\SupplyProductController;


class SupplyController extends StockController {

     /**
     * @var SupplyRepositoryEloquent
     */
    protected $supplyRepositoryEloquent;

     /**
     * @var SupplyProductRepositoryEloquent
     */
    protected $supplyProductRepositoryEloquent;

    /**
     * @var UserRepositoryEloquent
     */
    protected $userRepositoryEloquent;

    /**
     * @var ProductRepositoryEloquent
     */
    protected $productRepositoryEloquent;

    /**
     * @var StockRepositoryEloquent
     */
    protected $stockRepositoryEloquent;

    /**
     * @var StockProductRepositoryEloquent
     */
    protected $stockProductRepositoryEloquent;

    /**
     * @var SupplierRepositoryEloquent
     */
    protected $supplierRepositoryEloquent;

    public function __construct(SupplyRepositoryEloquent $supplyRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent, StockRepositoryEloquent $stockRepositoryEloquent,
    SupplyProductRepositoryEloquent $supplyProductRepositoryEloquent,
    SupplierRepositoryEloquent $supplierRepositoryEloquent,
    ProductRepositoryEloquent $productRepositoryEloquent,
        StockProductRepositoryEloquent $stockProductRepositoryEloquent) {
        parent::__construct();
        $this->supplyRepositoryEloquent = $supplyRepositoryEloquent;
        $this->supplyProductRepositoryEloquent = $supplyProductRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
        $this->productRepositoryEloquent = $productRepositoryEloquent;
        $this->stockRepositoryEloquent = $stockRepositoryEloquent;
        $this->stockProductRepositoryEloquent = $stockProductRepositoryEloquent;
        $this->supplierRepositoryEloquent = $supplierRepositoryEloquent;
    }
    
      /**
     * Return a listing of the resource.
     * @param SupplyIndexRequest $request
     * @return SuppliesResource
     */
    public function index(SupplyIndexRequest $request)
    {
        $donnees = $this->supplyRepositoryEloquent->paginate($this->nombrePage);   
        return new SuppliesResource($donnees);
    }

    /**
     * Show the specified resource.
     * @param SupplyIndexRequest $request
     * @param string $uuid
     * @return SupplyResource
     */ 
    public function show(SupplyIndexRequest $request, $uuid) {
        try {
            $item = $this->supplyRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return response()->json(['message' => 'Catégorie non trouvée'], 404);
            }
            return new SupplyResource($item);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param SupplyStoreRequest $request
     * @return SupplyResource
     */
    public function store(SupplyStoreRequest $request)
    {
        $attributes = $request->all();

        // create the supply
        $supply = DB::transaction(function () use ($attributes) {
            $stock = $this->stockRepositoryEloquent->findByUuid($attributes['stock_id'])->first();
            $attributes['stock_id'] = $stock->id;

            // Get the user executing the action
            $authUserId = 1;
            
            //Generate the supply number based on:
            //Three four letters APPRO followed by an hyphen
            //Then the date on the format YYYY-MM-DD followed by an hyphen
            //Then a random number

            $prefix = 'APPRO';
            $currentDate = now()->format('Y-m-d');
            $generatedSupplyNumber = '';

            do {
                $randomNumber = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
                $generatedSupplyNumber = $prefix . '-' . $currentDate. '-' . $randomNumber;
                $existingSupplyNumber = $this->supplyRepositoryEloquent->findByNumber($generatedSupplyNumber);
            } while ($existingSupplyNumber);

            $supplyData = [
                'stock_id' => $attributes['stock_id'],
                'numero' => $generatedSupplyNumber,
                'total' => $attributes['total'],
                'user_id' => $authUserId,
            ];

            $supply = $this->supplyRepositoryEloquent->create($supplyData);

                
            $supplyProductController = new SupplyProductController(
                $this->supplyProductRepositoryEloquent, 
                $this->userRepositoryEloquent, 
                $this->supplyRepositoryEloquent, 
                $this->productRepositoryEloquent,
                $this->stockProductRepositoryEloquent,
                $this->stockRepositoryEloquent,
                $this->supplierRepositoryEloquent);
            $supplyProductRequest = new SupplyProductStoreRequest();

            $supplyProducts = $attributes['supplyProducts'];

            // create the supply products
            foreach($supplyProducts as $supplyProduct)
            {   
                // Check if the lot_number is unique for the supply
                $existingLotNumber = $this->supplyProductRepositoryEloquent
                ->findByLotNumberAndSupply($supplyProduct['lot_number'], $supply->id);
                if ($existingLotNumber) {
                    // Throw an exception when the lot_number is not unique for the supply
                    abort(422, __("Erreur, numéro de lot en double pour cet approvisionnement"));
                }

                $product = $this->productRepositoryEloquent->findByUuid($supplyProduct['product_id'])->first();
                $supplyProduct['product_id'] = $product->id;

                // Check if the product is unique for the supply
                $existingProduct = $this->supplyProductRepositoryEloquent
                    ->findByProductAndSupply($product->id, $supply->id);

                if ($existingProduct) {
                    // Throw an exception when the product is not unique for the supply
                    abort(422, __("Erreur, produit en double pour cet approvisionnement"));
                }

                $supplier = $this->supplierRepositoryEloquent->findByUuid($supplyProduct['supplier_id'])->first();
                $supplyProduct['supplier_id'] = $supplier->id;

                $supplyProductRequest->lot_number = $supplyProduct['lot_number'];
                $supplyProductRequest->units_per_box = $supplyProduct['units_per_box'];
                $supplyProductRequest->expire_date = $supplyProduct['expire_date'];
                $supplyProductRequest->quantity = $supplyProduct['quantity'];
                $supplyProductRequest->purchase_price = $supplyProduct['purchase_price'];
                $supplyProductRequest->profit_margin = $supplyProduct['profit_margin'];
                $supplyProductRequest->supply_id = $supply->id;
                $supplyProductRequest->product_id = $supplyProduct['product_id'];
                $supplyProductRequest->supplier_id = $supplyProduct['supplier_id'];

                // create the supply product
                $supplyProduct = $supplyProductController->store($supplyProductRequest);
            }
            return $supply;
        });

        $supply = $supply->fresh();
        return new SupplyResource($supply);
    }
    
    /**
     * Update the specified resource in storage.
     * @param SupplyUpdateRequest $request
     * @return SupplyResource
     */
    public function update(SupplyUpdateRequest $request, $uuid)
    {
        $supply = $this->supplyRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributes = $request->all();

        // Get the user executing the action
        $authUserId = 1;
        $attributs['user_id'] = $authUserId;

        $supply = $this->supplyRepositoryEloquent->update($attributes, $supply->id);
        $supply = $supply->fresh();

        return new SupplyResource($supply);
    }

    /**
     * Remove the specified resource from storage.
     * @param SupplyDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(SupplyDeleteRequest $request, $uuid)
    {
        $supply = $this->supplyRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        
        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }    

    public function getProducts($uuid)
    {
        $supply = $this->supplyRepositoryEloquent->findByUuid($uuid)->first();

        $supplyProduct =  SupplyProduct::where('supply_id', $supply->id)->get();
        
        return new SupplyProductsResource($supplyProduct);
    }
}
