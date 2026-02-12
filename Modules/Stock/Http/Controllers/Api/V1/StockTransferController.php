<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Entities\Product;
use Modules\Stock\Entities\TypeProduct;
use Modules\Stock\Entities\StockProduct;
use Modules\Stock\Entities\StockTransfer;
use App\Repositories\UserRepositoryEloquent;
use Modules\Stock\Entities\StockTransferProduct;
use Modules\Stock\Http\Controllers\StockController;
use Modules\Stock\Http\Resources\StockTransferResource;
use Modules\Stock\Repositories\StockRepositoryEloquent;
use Modules\Stock\Http\Resources\StockTransfersResource;
use Modules\Stock\Repositories\ProductRepositoryEloquent;
use Modules\Stock\Http\Requests\StockTransferIndexRequest;
use Modules\Stock\Http\Requests\StockTransferStoreRequest;
use Modules\Stock\Http\Requests\StockTransferDeleteRequest;
use Modules\Stock\Http\Requests\StockTransferUpdateRequest;
use Modules\Stock\Repositories\StockProductRepositoryEloquent;
use Modules\Stock\Http\Resources\StockTransferProductsResource;
use Modules\Stock\Repositories\StockTransferRepositoryEloquent;
use Modules\Stock\Http\Requests\StockTransferProductStoreRequest;
use Modules\Administration\Repositories\ServiceRepositoryEloquent;
use Modules\Stock\Repositories\StockTransferProductRepositoryEloquent;
use Modules\Stock\Http\Controllers\Api\V1\StockTransferProductController;
use Modules\Stock\Http\Resources\StockTransferProductResource;

class StockTransferController extends StockController {

     /**
     * @var StockTransferRepositoryEloquent
     */
    protected $stockTransferRepositoryEloquent;

     /**
     * @var StockTransferProductRepositoryEloquent
     */
    protected $stockTransferProductRepositoryEloquent;

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
     * @var ServiceRepositoryEloquent
     */
    protected $serviceRepositoryEloquent;

    public function __construct(StockTransferRepositoryEloquent $stockTransferRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent, StockRepositoryEloquent $stockRepositoryEloquent,
    StockTransferProductRepositoryEloquent $stockTransferProductRepositoryEloquent,
    ProductRepositoryEloquent $productRepositoryEloquent,
        StockProductRepositoryEloquent $stockProductRepositoryEloquent) {
        parent::__construct();
        $this->stockTransferRepositoryEloquent = $stockTransferRepositoryEloquent;
        $this->stockTransferProductRepositoryEloquent = $stockTransferProductRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
        $this->productRepositoryEloquent = $productRepositoryEloquent;
        $this->stockRepositoryEloquent = $stockRepositoryEloquent;
        $this->stockProductRepositoryEloquent = $stockProductRepositoryEloquent;
    }
    
      /**
     * Return a listing of the resource.
     * @param StockTransferIndexRequest $request
     * @return StockTransfersResource
     */
    public function index(StockTransferIndexRequest $request)
    {
        $donnees = $this->stockTransferRepositoryEloquent->paginate($this->nombrePage);   
        return new StockTransfersResource($donnees);
    }

    /**
     * Show the specified resource.
     * @param StockTransferIndexRequest $request
     * @param string $uuid
     * @return StockTransferResource
     */ 
    public function show(StockTransferIndexRequest $request, $uuid) {
        try {
            $item = $this->stockTransferRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return response()->json(['message' => 'Transfert de stock non trouvé'], 404);
            }
            return new StockTransferResource($item);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param StockTransferStoreRequest $request
     * @return StockTransferResource
     */
    public function store(Request $request)
    {
        $attributes = $request->all();

        // create the stockTransfer
        $stockTransfer = DB::transaction(function () use ($attributes) {
            $modelName = $attributes['model_name'];

            $model = $modelName == 'Stock'
            ? $this->stockRepositoryEloquent->findByUuid($attributes['model_id'])->first()
            : $this->serviceRepositoryEloquent->findByUuid($attributes['model_id'])->first();

            $attributes['model_id'] = $model->id;
            $attributes['from_stock_id'] = $this->stockRepositoryEloquent->findByUuid($attributes['from_stock_id'])->first()->id;

            // Get the user executing the action
            $authUserId = 1;

            $stockTransferData = [
                'model_name' => $attributes['model_name'],
                'model_id' => $attributes['model_id'],
                'from_stock_id' => $attributes['from_stock_id'],
                'user_id' => $authUserId,
            ];
            $stockTransfer = $this->stockTransferRepositoryEloquent->create($stockTransferData);
            $stockTransfer = $stockTransfer->fresh();

            $stockTransferProducts = $attributes['stockTransferProducts'];
            // create the stockTransfer products
            foreach($stockTransferProducts as $stockTransferProduct)
            {   
                $product = $this->productRepositoryEloquent->findByUuid($stockTransferProduct['product_id'])->first();
                $quantityToTransfer = $stockTransferProduct['quantity_to_transfer'];

                //il faudrait pouvoir intégrer cette vérification backend
                // // Check if the product is unique for the stockTransfer
                // $existingProduct = $this->stockTransferProductRepositoryEloquent
                //     ->findByProductAndStockTransfer($product->id, $stockTransfer->id);

                // if ($existingProduct) {
                //     // Throw an exception when the product is not unique for the stockTransfer
                //     abort(422, __("Erreur, produit en double pour cet approvisionnement"));
                // }
                $this->transferProduct($product->id, $quantityToTransfer, $stockTransfer);
            }
            return $stockTransfer;
        });

        return new StockTransferResource($stockTransfer);
    }
    
    /**
     * Update the specified resource in storage.
     * @param StockTransferUpdateRequest $request
     * @return StockTransferResource
     */
    public function update(StockTransferUpdateRequest $request, $uuid)
    {
        $stockTransfer = $this->stockTransferRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributes = $request->all();

        // Get the user executing the action
        $authUserId = 1;
        $attributs['user_id'] = $authUserId;

        $stockTransfer = $this->stockTransferRepositoryEloquent->update($attributes, $stockTransfer->id);
        $stockTransfer = $stockTransfer->fresh();

        return new StockTransferResource($stockTransfer);
    }

    /**
     * Remove the specified resource from storage.
     * @param StockTransferDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(StockTransferDeleteRequest $request, $uuid)
    {
        $stockTransfer = $this->stockTransferRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        
        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }    

    public function getProducts($uuid)
    {
        $stockTransfer = $this->stockTransferRepositoryEloquent->findByUuid($uuid)->first();

        $stockTransferProduct =  StockTransferProduct::where('stock_transfer_id', $stockTransfer->id)->get();
        
        return new StockTransferProductsResource($stockTransferProduct);
    }

    
    public function transferProduct($productIdentifier, $quantityToTransfer, $stockTransfer)
    {   
        $quantityInStock = $this->getProductQuantityInStock($productIdentifier, $stockTransfer->from_stock_id);
        $this->checkQuantityInStock($quantityToTransfer, $quantityInStock);

        $quantityTransfered = 0;
        $quantityRemainingToTransfer = $quantityToTransfer; 
        $productLotsInFIFO = $this->getProductLotsInFIFOOrderInStock($productIdentifier, $stockTransfer->from_stock_id);

        $stockTransferProductController = new StockTransferProductContoller(
            $this->stockTransferProductRepositoryEloquent,
            $this->stockTransferRepositoryEloquent,
            $this->stockProductRepositoryEloquent,
            $this->userRepositoryEloquent,
            $this->stockRepositoryEloquent,
            $this->productRepositoryEloquent
        );
        $stockTransferProductRequest = new StockTransferProductStoreRequest();
        
        foreach ($productLotsInFIFO as $productLotInFIFO) {
            $stockTransferProductRequest->stock_product_id = $productLotInFIFO->id;
            $stockTransferProductRequest->stock_transfer_id = $stockTransfer->id;

            if ($quantityTransfered < $quantityToTransfer) {
                if ($productLotInFIFO->quantity >= $quantityRemainingToTransfer) {
                    $quantityTransfered += $quantityRemainingToTransfer;

                    $stockTransferProductRequest->quantity_transfered = $quantityRemainingToTransfer;

                    // create the stockTransfer product
                    $stockTransferProduct = $stockTransferProductController->store($stockTransferProductRequest);
                    $quantityRemainingToTransfer = 0; 
                } elseif ($productLotInFIFO->quantity < $quantityRemainingToTransfer) {
                    $quantityTransfered += $productLotInFIFO->quantity;
                    $quantityRemainingToTransfer -= $productLotInFIFO->quantity;

                    $stockTransferProductRequest->quantity_transfered = $productLotInFIFO->quantity;

                    // create the stockTransfer product
                    $stockTransferProduct = $stockTransferProductController->store($stockTransferProductRequest);
                } 
            }   
        }
    }

    public function getProductLotsInFIFOOrderInStock($productId, $stockId)
    {
        // Get all lots of the corresponding product in the specified stock, ordered by expire date ascending and where the quantity is not null
        $productLotsInFIFO =  StockProduct::where('product_id', $productId)
        ->where('stock_id', $stockId)
        ->where('quantity', '>', 0)
        ->orderBy('expire_date', 'asc')
        ->get();

        return $productLotsInFIFO;
    }

    private function checkQuantityInStock($quantityToTransfer, $quantityInStock)
    {
        if ($quantityToTransfer > $quantityInStock) {
            throw new \RuntimeException('La quantité à transférer excède la quantité en stock');
        }
    }   
    
    public function getProductQuantityInStock($productId, $stockId)
    {
        $products = StockProduct::where('product_id', $productId)
        ->where('stock_id', $stockId)
        ->where('quantity', '>', 0)
        ->get();

        $quantity = 0;

        foreach ($products as $product) {
            $quantity += $product->quantity;
        }

        return $quantity;
    }

    // Handle exceptions and return a JSON response
    private function handleException(\Exception $e)
    {
        return reponse_json_transform(['message' => $e->getMessage()], $e->getCode() ?: 400);
    }

    // public function getProducts($uuid)
    // {
    //     $supply = $this->stockTransferRepositoryEloquent->findByUuid($uuid)->first();

    //     $supplyProduct =  StockTransferProduct::where('supply_id', $supply->id)->get();
        
    //     return new StockTransferProductsResource($supplyProduct);
    // }
}
