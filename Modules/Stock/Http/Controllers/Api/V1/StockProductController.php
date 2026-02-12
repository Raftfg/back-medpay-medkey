<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Modules\Stock\Entities\Stock;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Entities\Destock;
use Modules\Stock\Entities\Product;
use Modules\Stock\Entities\TypeProduct;
use Modules\Stock\Entities\StockProduct;
use App\Repositories\UserRepositoryEloquent;
use Modules\Stock\Http\Resources\ProductsResource;
use Modules\Stock\Http\Controllers\StockController;
use Modules\Stock\Http\Resources\StockProductResource;
use Modules\Stock\Http\Resources\StockProductsResource;
use Modules\Stock\Repositories\StockRepositoryEloquent;
use Modules\Stock\Http\Requests\StockProductIndexRequest;
use Modules\Stock\Http\Requests\StockProductStoreRequest;
use Modules\Stock\Repositories\ProductRepositoryEloquent;
use Modules\Stock\Http\Requests\StockProductDeleteRequest;
use Modules\Stock\Http\Requests\StockProductUpdateRequest;
use Modules\Stock\Http\Resources\DestockRepositoryEloquent;
use Modules\Stock\Repositories\StockProductRepositoryEloquent;

class StockProductController extends StockController {

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

    public function __construct(StockProductRepositoryEloquent $stockProductRepositoryEloquent,
    UserRepositoryEloquent $userRepositoryEloquent,
    StockRepositoryEloquent $stockRepositoryEloquent,
    ProductRepositoryEloquent $productRepositoryEloquent) {
        parent::__construct();
        $this->stockProductRepositoryEloquent = $stockProductRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
        $this->stockRepositoryEloquent = $stockRepositoryEloquent;
        $this->productRepositoryEloquent = $productRepositoryEloquent;
    }
    
    /**
     * Return a listing of the resource.
     * @param StockProductIndexRequest $request
     * @return StockProductsResource
     */
    public function index(StockProductIndexRequest $request)
    {
        $donnees = $this->stockProductRepositoryEloquent->paginate($this->nombrePage);   
        return new StockProductsResource($donnees);
    }

    /**
     * Show the specified resource.
     * @param StockProductIndexRequest $request
     * @param string $uuid
     * @return StockProductResource
     */ 
    public function show(StockProductIndexRequest $request, $uuid) {
        try {
            $item = $this->stockProductRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return response()->json(['message' => 'Produit de stock non trouvé'], 404);
            }
            return new StockProductResource($item);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param StockProductStoreRequest $request
     * @return StockProductResource
     */
    public function store(StockProductStoreRequest $request)
    {
        $attributs['lot_number'] = $request->lot_number;
        $attributs['units_per_box'] = $request->units_per_box;
        $attributs['expire_date'] = $request->expire_date;
        $attributs['quantity'] = $request->quantity;
        $attributs['purchase_price'] = $request->purchase_price;
        $attributs['selling_price'] = $request->selling_price;
        $attributs['stock_id'] = $request->stock_id;
        $attributs['product_id'] = $request->product_id;

        $stockProduct = DB::transaction(function () use ($attributs) {
            // Get the user executing the action
            $authUserId = 1;
            $attributs['user_id'] = $authUserId;

            $stockProduct = $this->stockProductRepositoryEloquent->create($attributs);
            return $stockProduct;
        });

        $stockProduct = $stockProduct->fresh();
        return new StockProductResource($stockProduct);
    }
    
    /**
     * Update the specified resource in storage.
     * @param StockProductUpdateRequest $request
     * @return StockProductResource
     */
    public function update(StockProductUpdateRequest $request, $uuid)
    {
        $stockProduct = $this->stockProductRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        // Get the user executing the action
        $authUserId = 1;
        $attributs['user_id'] = $authUserId;

        $stockProduct = $this->stockProductRepositoryEloquent->update($attributs, $stockProduct->id);
        $stockProduct = $stockProduct->fresh();

        return new StockProductResource($stockProduct);
    }

    /**
     * Remove the specified resource from storage.
     * @param StockProductDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(StockProductDeleteRequest $request, $uuid)
    {
        $stockProduct = $this->stockProductRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression

        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }    

    public function getDrugsInStock($uuid)
    {
        $typeProduct = TypeProduct::where('name', 'Drugs')->first();

        if (!$typeProduct) {
            return response()->json(['error' => 'Type de produit non trouvé'], 404);
        }

        // Retrieve the stock based on the $stockId
        $stock = $this->stockRepositoryEloquent->findByUuid($uuid)->first();
        $stock = Stock::find($stock->id);

        if (!$stock) {
            return response()->json(['error' => 'Stock non trouvé'], 404);
        }

        // Retrieve the drug products related to this stock
        $drugProducts = $stock->stockProducts()
            ->whereHas('product', function ($query) use ($typeProduct) {
                $query->where('type_id', $typeProduct->id); 
            })
            ->where('quantity', '>', 0)
            ->get();

        return new StockProductsResource($drugProducts);
    }

    public function getAllDrugsAvailable()
    {
        $typeProduct = TypeProduct::where('name', 'Drugs')->first();

        if (!$typeProduct) {
            return response()->json(['error' => 'TypeProduct not found'], 404);
        }
        
        try {
            // Retrieve all products in the specified stock
            $productsInStock = Product::join('stock_products', 'products.id', '=', 'stock_products.product_id')
            ->where('products.type_id', $typeProduct->id)
            ->where('stock_products.quantity', '>', 0) // Adjust this condition based on your requirements
            ->distinct('products.id') // To get distinct products based on IDs
            ->get([
                'products.*'
            ]);

            return new ProductsResource($productsInStock);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }


    public function getAllDrugsAvailableInAllStocks()
    {
        $typeProduct = TypeProduct::where('name', 'Drugs')->first();

        if (!$typeProduct) {
            return response()->json(['error' => 'TypeProduct not found'], 404);
        }

        // Retrieve all drug products available
        $allDrugsInStocks = StockProduct::whereHas('product', function ($query) use ($typeProduct) {
            $query->where('type_id', $typeProduct->id); // Assuming 'type_id' is the product type identifier
        })
        ->where('quantity', '>', 0)
        ->get();

        return new StockProductsResource($allDrugsInStocks);
    }



    public function getConsumablesInStock($uuid)
    {
        $typeProduct = TypeProduct::where('name', 'Consumables')->first();

        if (!$typeProduct) {
            return response()->json(['error' => 'Type de produit non trouvé'], 404);
        }

        // Retrieve the stock based on the $stockId
        $stock = $this->stockRepositoryEloquent->findByUuid($uuid)->first();
        $stock = Stock::find($stock->id);

        if (!$stock) {
            return response()->json(['error' => 'Stock non trouvé'], 404);
        }

        // Retrieve the consumable products related to this stock
        $consumableProducts = $stock->stockProducts()
            ->whereHas('product', function ($query) use ($typeProduct) {
                $query->where('type_id', $typeProduct->id); // Assuming 'type_id' is the product type identifier
            })
            ->where('quantity', '>', 0)
            ->get();

        return new StockProductsResource($consumableProducts);
    }

    public function getAllConsumablesAvailableInAllStocks()
    {
        $typeProduct = TypeProduct::where('name', 'Consumables')->first();

        if (!$typeProduct) {
            return response()->json(['error' => 'TypeProduct not found'], 404);
        }

        // Retrieve all consumables products available
        $allConsumablesInStocks = StockProduct::whereHas('product', function ($query) use ($typeProduct) {
            $query->where('type_id', $typeProduct->id); // Assuming 'type_id' is the product type identifier
        })
        ->where('quantity', '>', 0)
        ->get();

        return new StockProductsResource($allConsumablesInStocks);
    }

    public function getNotebooksAndCardsInStock($uuid)
    {
        $typeProduct = TypeProduct::where('name', 'Notebooks and cards')->first();

        if (!$typeProduct) {
            return response()->json(['error' => 'Type de produit non trouvé'], 404);
        }

        // Retrieve the stock based on the $stockId
        $stock = $this->stockRepositoryEloquent->findByUuid($uuid)->first();
        $stock = Stock::find($stock->id);

        if (!$stock) {
            return response()->json(['error' => 'Stock non trouvé'], 404);
        }

        // Retrieve the consumable products related to this stock
        $notebookProducts = $stock->stockProducts()
            ->whereHas('product', function ($query) use ($typeProduct) {
                $query->where('type_id', $typeProduct->id); // Assuming 'type_id' is the product type identifier
            })
            ->where('quantity', '>', 0)
            ->get();

        return new StockProductsResource($notebookProducts);
    }

    public function getAllNotebooksAvailableInAllStocks()
    {
        $typeProduct = TypeProduct::where('name', 'Notebooks and cards')->first();

        if (!$typeProduct) {
            return response()->json(['error' => 'TypeProduct not found'], 404);
        }

        // Retrieve all notebooks products available
        $allNotebooksInStocks = StockProduct::whereHas('product', function ($query) use ($typeProduct) {
            $query->where('type_id', $typeProduct->id); // Assuming 'type_id' is the product type identifier
        })
        ->where('quantity', '>', 0)
        ->get();

        return new StockProductsResource($allNotebooksInStocks);
    }

    public function getProductsInStock($uuid)
    {
        // Retrieve the stock based on the $stockId
        $stock = $this->stockRepositoryEloquent->findByUuid($uuid)->first();
        $stock = Stock::find($stock->id);

        if (!$stock) {
            return response()->json(['error' => 'Stock not found'], 404);
        }

        // Retrieve the products related to this stock
        $products = $stock->stockProducts()->get();

        return new StockProductsResource($products);
    }

    public function getOldestProductLotInStock($productId, $stockId)
    {
        // Get the oldest product lot in the specified stock
        $product =  StockProduct::where('product_id', $productId)
        ->where('stock_id', $stockId)
        ->where('quantity', '>', 0)
        ->orderBy('expire_date', 'asc')
        ->first();

        return $product;
    }

    //Product lot data by stockProductUuid
    public function getProductLotByStockProduct($stockProductUuid) {
        $productLot = $this->stockProductRepositoryEloquent->findByUuid($stockProductUuid)->first();
        return $productLot;
    }

    public function getProductLotQuantityByStockProduct($stockProductUuid) {
        $productLot = $this->getProductLotByStockProduct($stockProductUuid);
        return $productLot->quantity;
    }
    public function getProductLotDetailsByStockProductService($stockProductUuid) {
        try {
            $productLot = $this->stockProductRepositoryEloquent->findByUuid($stockProductUuid)->first();
            if (!$productLot) {
                throw new \RuntimeException('Lot non trouvé');
            }
            $data = [
                "lot_number" => $productLot->lot_number,
                "expire_date" => $productLot->expire_date,
                "quantity" => $productLot->quantity,
            ];
            return reponse_json_transform($data);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getProductLotPriceByStockProduct($stockProductUuid) {
        $productLot = $this->getProductLotByStockProduct($stockProductUuid);
        return $productLot->selling_price;
    }
    
    public function getAllProductsInStockService($stockUuid = null)
    {
        try {
            // Check the stock
            $stock = $this->checkStockToDestock($stockUuid);

            // Retrieve all products in the specified stock
            $productsInStock = Product::join('stock_products', 'products.id', '=', 'stock_products.product_id')
            ->where('stock_products.stock_id', $stock->id)
            ->where('stock_products.quantity', '>', 0) // Adjust this condition based on your requirements
            ->distinct('products.id') // To get distinct products based on IDs
            ->get([
                'products.*'
            ]);

            return new ProductsResource($productsInStock);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getProductLotsInFIFOOrderInStockService($productUuid, $stockUuid = null)
    {
        try { 
            // Check the stock
            $stock = $this->checkStockToDestock($stockUuid);
            // Check if the product exists
            $product = $this->getProductByUuidOrCodeOrId($productUuid);

            if (!$product) {
                throw new \RuntimeException('Produit non trouvé');
            }

            // $productLotsInFIFO =  $this->getProductLotsInFIFOOrderInStock($product->id, $stock->id);
            // Get all lots of the corresponding product in the specified stock, ordered by expire date ascending and where the quantity is not null
            $productLotsInFIFO = StockProduct::join('products', 'stock_products.product_id', '=', 'products.id')
            ->where('stock_products.product_id', $product->id)
            ->where('stock_products.stock_id', $stock->id)
            ->where('stock_products.quantity', '>', 0)
            ->orderBy('stock_products.expire_date', 'asc')
            ->get([
                'products.code as code',
                'stock_products.*'
            ]);

            return response()->json(['data' => $productLotsInFIFO], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
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

    public function getProductByUuidOrCodeOrId($productIdentifier)
    {
        // If the productUuid looks like an uuid get the product by uuid
        if (preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/', $productIdentifier)) {
            return $this->productRepositoryEloquent->findByUuid($productIdentifier)->first();
        }

        // If the product is an Id
        if (is_numeric($productIdentifier)) {
            return $this->productRepositoryEloquent->find($productIdentifier);
        }

        // Assume it's a product code and query by code
        return $this->productRepositoryEloquent->findByField('code', $productIdentifier)->first();
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

    public function getProductQuantity($productIdentifier, $stockUuid = null)
    {
        try {
            $product = $this->getProductByUuidOrCodeOrId($productIdentifier);
            if (!$product) {
                return reponse_json_transform(['message' => 'Produit non trouvé'], 404);
            }

            try {   
                $stock = $this->checkStockToDestock($stockUuid);
            } catch (\Exception $e) {
                return $this->handleException($e);
            }

            $quantity = $this->getProductQuantityInStock($product->id, $stock->id);

            return reponse_json_transform(['product' => $product->uuid, 'unit_quantity' => $quantity], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    public function updateProductQuantityInStockDB($stockProductId, $newQuantity)
    {
        try {
            // Find and update the product lot in the specified stock
            $productLot =  StockProduct::find($stockProductId);
    
            if ($productLot) {
                $productLot->quantity = $newQuantity;
                $productLot->save();
            }
        } catch (\Exception $e) {
            // Handle any exceptions that might occur during the update
            throw $e;
        }
    }

    // Validation of the quantity to destock
    private function validateQuantityToDestock($quantityToRetrieve)
    {
        if (!is_numeric($quantityToRetrieve) || $quantityToRetrieve < 1) {
            throw new \InvalidArgumentException('Quantité à déstocker invalide');
        }
    }

    // Check if the quantity to destock is above the quantity in stock
    private function checkQuantityInStock($quantityToRetrieve, $quantityInStock)
    {
        if ($quantityToRetrieve > $quantityInStock) {
            throw new \RuntimeException('La quantité à déstocker excède la quantité en stock');
        }
    }

    // Check if there is a stock to destock from
    private function checkStockToDestock($stockUuid)
    {
        // If the $stockUuid is provided, retrieve its corresponding stock
        // else get the sale pharmacy stock by default 
        $stock = $stockUuid
            ? $this->stockRepositoryEloquent->findByUuid($stockUuid)->first()
            : $this->stockRepositoryEloquent->findByField('for_pharmacy_sale', 1)->first();

        // If no stock with the above criteria exists, return an error
        if (!$stock) {
            throw new \RuntimeException('Aucun stock trouvé pour effectuer le déstockage.');

        }
        return $stock;
    }

    public function destockProductByLotUuid($quantityToRetrieve, $stockProductUuid, $quantityOrdered, $referenceFacture)
    {   
        // Check the product lot existance by stockProductUuid
        // Searching by stockProductUuid will give a more accurate product lot because a product can have the same role more than once in the same stock.
        $productLot = $this->getProductLotByStockProduct($stockProductUuid);
        if (!$productLot) {
            throw new \RuntimeException('Lot de produit non trouvé');
        }

        $this->validateQuantityToDestock($quantityToRetrieve);
        $quantityInStock = $productLot->quantity;
        $this->checkQuantityInStock($quantityToRetrieve, $quantityInStock);

        $newQuantity = $productLot->quantity - $quantityToRetrieve;
        $this->updateProductQuantityInStockDB($productLot->id, $newQuantity);

        // Now, let's update the destock table
        // $destock = $this->getDestockByStockIdAndReferenceId($productLot->stock_id, $productLot->reference_id);
        $destock = Destock::where('stock_product_id',$productLot->id)
                            ->where('reference_facture', $referenceFacture)
                            ->first();

        if ($destock) {
            // If destock entry already exists, update it
            $updatedQuantity = $destock->quantity_retrieved + $quantityToRetrieve;
            // $this->updateDestockQuantity($destock->id, $updatedQuantity);
            $destock->update(['quantity_retrieved' => $updatedQuantity]);
        } else {
            // If destock entry doesn't exist, create a new one
            // $this->createDestockEntry($productLot->stock_id, $productLot->reference_id, $quantityToRetrieve);
            Destock::create([
                'stock_product_id' => $productLot->id,
                'reference_facture' => $referenceFacture,
                'quantity_retrieved' => $quantityToRetrieve,
                'quantity_ordered' => $quantityOrdered,
                'user_id' => auth()->user()->id,
                // Add other fields as needed
            ]);
        }
    }

    public function destockProductsByLotUuid(Request $request)
    {  

        $products = $request['products'];
        $referenceFacture = $request['reference_facture'];
        // return $products;

        // try {   
            DB::beginTransaction();
   
            foreach ($products as $product) {
                $this->destockProductByLotUuid($product['quantity_to_retrieve'], $product['lot_uuid'], $product['quantity_ordered'], $referenceFacture);
            }
    
            DB::commit();
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     return $this->handleException($e);
        // }

        return reponse_json_transform(['message' => 'Produits déstockés avec succès'], 200);
    }

    public function destockProduct($productIdentifier, $quantityToRetrieve, $stockUuid = null)
    {   
        // try {   
            try {   
                $stock = $this->checkStockToDestock($stockUuid);
            } catch (\Exception $e) {
                return $this->handleException($e);
            }

            // Check if the product exists
            $product = $this->getProductByUuidOrCodeOrId($productIdentifier);
            if (!$product) {
                throw new \RuntimeException('Produit non trouvé');
            }

            $this->validateQuantityToDestock($quantityToRetrieve);
            $quantityInStock = $this->getProductQuantityInStock($product->id, $stock->id);
            $this->checkQuantityInStock($quantityToRetrieve, $quantityInStock);

            $quantityRetrieved = 0;
            $quantityRemainingToRetrieve = $quantityToRetrieve; 
            $productLotsInFIFO = $this->getProductLotsInFIFOOrderInStock($product->id, $stock->id);

            // Get the quantity of the product in the stock
            foreach ($productLotsInFIFO as $productLotInFIFO) {
                if ($quantityRetrieved < $quantityToRetrieve) {
                    if ($productLotInFIFO->quantity > $quantityRemainingToRetrieve) {
                        $quantityRetrieved += $quantityRemainingToRetrieve;
                        $newQuantity = $productLotInFIFO->quantity - $quantityRemainingToRetrieve;
                        $this->updateProductQuantityInStockDB($productLotInFIFO->id, $newQuantity);
                        $quantityRemainingToRetrieve = 0; 
                    } elseif ($productLotInFIFO->quantity <= $quantityRemainingToRetrieve) {
                        $quantityRetrieved += $productLotInFIFO->quantity;
                        $quantityRemainingToRetrieve -= $productLotInFIFO->quantity;
                        $this->updateProductQuantityInStockDB($productLotInFIFO->id, 0);
                    } 
                }   
            }

        //     return reponse_json_transform(['message' => 'Produit déstocké avec succès'], 200);
        // } catch (\Exception $e) {
        //     return $this->handleException($e);
        // }
    }

    public function destockProducts(Request $request)
    {
        $stockUuid = $request['stock_id'];
        $products = $request['products'];

        try {   
            $stock = $this->checkStockToDestock($stockUuid);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }


        try {   
            DB::beginTransaction();
   
            foreach ($products as $product) {
                $this->destockProduct($product['product_id'], $product['quantity_to_retrieve'], $stockUuid);
            }
    
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e);
        }

        return reponse_json_transform(['message' => 'Produits déstockés avec succès'], 200);
    }

    // Handle exceptions and return a JSON response
    private function handleException(\Exception $e)
    {
        return reponse_json_transform(['message' => $e->getMessage()], $e->getCode() ?: 400);
    }

    public function countDrugsInStock($stockUuid)
    {
        try {
            // Check the stock
            $stock = $this->checkStockToDestock($stockUuid);
    
            // Get the TypeProduct for drugs
            $typeProduct = TypeProduct::where('name', 'Drugs')->first();
    
            if (!$typeProduct) {
                return response()->json(['error' => 'TypeProduct not found'], 404);
            }
    
            // Retrieve the stock based on the $stockId
            $stock = $this->stockRepositoryEloquent->findByUuid($stockUuid)->first();
            $stock = Stock::find($stock->id);
    
            if (!$stock) {
                return response()->json(['error' => 'Stock not found'], 404);
            }
    
            // Retrieve and count the drugs in the specified stock
            $drugCount = $stock->stockProducts()
                ->whereHas('product', function ($query) use ($typeProduct) {
                    $query->where('type_id', $typeProduct->id); // Assuming 'type_id' is the product type identifier
                })
                ->count();
    
            return response()->json(['drug_count' => $drugCount], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function countDistinctDrugsInAllStocks()
    {
        try {
            // Get the TypeProduct for drugs
            $typeProduct = TypeProduct::where('name', 'Drugs')->first();
    
            if (!$typeProduct) {
                return response()->json(['error' => 'TypeProduct not found'], 404);
            }
    
            // Retrieve all stocks
            $stocks = Stock::all();
    
            // Initialize an array to store distinct product IDs and their total quantity
            $distinctDrugsInfo = [];
    
            // Iterate over each stock and collect distinct drugs and their total quantity
            foreach ($stocks as $stock) {
                // Retrieve distinct drugs in the current stock with their total quantity
                $distinctDrugs = $stock->stockProducts()
                    ->whereHas('product', function ($query) use ($typeProduct) {
                        $query->where('type_id', $typeProduct->id); // Assuming 'type_id' is the product type identifier
                    })
                    ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
                    ->groupBy('product_id') // Ensure uniqueness based on product_id
                    ->havingRaw('SUM(quantity) > 0') // Exclude products with total quantity equal to 0
                    ->get();
    
                // Merge the distinct product IDs and their total quantity into the array
                foreach ($distinctDrugs as $distinctDrug) {
                    $productId = $distinctDrug->product_id;
                    $totalQuantity = isset($distinctDrugsInfo[$productId]) ? $distinctDrugsInfo[$productId] : 0;
                    $totalQuantity += $distinctDrug->total_quantity;
                    $distinctDrugsInfo[$productId] = $totalQuantity;
                }
            }
    
            // Count the total number of distinct drugs
            $totalDistinctDrugCount = count($distinctDrugsInfo);
    
            return response()->json(['total_distinct_drug_count' => $totalDistinctDrugCount, 'distinct_drugs_info' => $distinctDrugsInfo], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function countDistinctConsumablesInAllStocks()
    {
        try {
            // Get the TypeProduct for drugs
            $typeProduct = TypeProduct::where('name', 'Notebooks and cards')->first();
    
            if (!$typeProduct) {
                return response()->json(['error' => 'TypeProduct not found'], 404);
            }
    
            // Retrieve all stocks
            $stocks = Stock::all();
    
            // Initialize an array to store distinct product IDs and their total quantity
            $distinctDrugsInfo = [];
    
            // Iterate over each stock and collect distinct drugs and their total quantity
            foreach ($stocks as $stock) {
                // Retrieve distinct drugs in the current stock with their total quantity
                $distinctDrugs = $stock->stockProducts()
                    ->whereHas('product', function ($query) use ($typeProduct) {
                        $query->where('type_id', $typeProduct->id); // Assuming 'type_id' is the product type identifier
                    })
                    ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
                    ->groupBy('product_id') // Ensure uniqueness based on product_id
                    ->havingRaw('SUM(quantity) > 0') // Exclude products with total quantity equal to 0
                    ->get();
    
                // Merge the distinct product IDs and their total quantity into the array
                foreach ($distinctDrugs as $distinctDrug) {
                    $productId = $distinctDrug->product_id;
                    $totalQuantity = isset($distinctDrugsInfo[$productId]) ? $distinctDrugsInfo[$productId] : 0;
                    $totalQuantity += $distinctDrug->total_quantity;
                    $distinctDrugsInfo[$productId] = $totalQuantity;
                }
            }
    
            // Count the total number of distinct drugs
            $totalDistinctDrugCount = count($distinctDrugsInfo);
    
            return response()->json(['total_distinct_drug_count' => $totalDistinctDrugCount, 'distinct_drugs_info' => $distinctDrugsInfo], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    public function countDistinctNotebooksAndCardsInAllStocks()
    {
        try {
            // Get the TypeProduct for drugs
            $typeProduct = TypeProduct::where('name', 'Consumables')->first();
    
            if (!$typeProduct) {
                return response()->json(['error' => 'TypeProduct not found'], 404);
            }
    
            // Retrieve all stocks
            $stocks = Stock::all();
    
            // Initialize an array to store distinct product IDs and their total quantity
            $distinctDrugsInfo = [];
    
            // Iterate over each stock and collect distinct drugs and their total quantity
            foreach ($stocks as $stock) {
                // Retrieve distinct drugs in the current stock with their total quantity
                $distinctDrugs = $stock->stockProducts()
                    ->whereHas('product', function ($query) use ($typeProduct) {
                        $query->where('type_id', $typeProduct->id); // Assuming 'type_id' is the product type identifier
                    })
                    ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
                    ->groupBy('product_id') // Ensure uniqueness based on product_id
                    ->havingRaw('SUM(quantity) > 0') // Exclude products with total quantity equal to 0
                    ->get();
    
                // Merge the distinct product IDs and their total quantity into the array
                foreach ($distinctDrugs as $distinctDrug) {
                    $productId = $distinctDrug->product_id;
                    $totalQuantity = isset($distinctDrugsInfo[$productId]) ? $distinctDrugsInfo[$productId] : 0;
                    $totalQuantity += $distinctDrug->total_quantity;
                    $distinctDrugsInfo[$productId] = $totalQuantity;
                }
            }
    
            // Count the total number of distinct drugs
            $totalDistinctDrugCount = count($distinctDrugsInfo);
    
            return response()->json(['total_distinct_drug_count' => $totalDistinctDrugCount, 'distinct_drugs_info' => $distinctDrugsInfo], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
    
    // public function countDistinctConsumablesInAllStocks()
    // {
    //     try {
    //         // Get the TypeProduct for drugs
    //         $typeProduct = TypeProduct::where('name', 'Consumables')->first();

    //         if (!$typeProduct) {
    //             return response()->json(['error' => 'TypeProduct not found'], 404);
    //         }

    //         // Retrieve all stocks
    //         $stocks = Stock::all();

    //         // Initialize an array to keep track of distinct product IDs
    //         $distinctProductIds = [];

    //         // Iterate over each stock and count distinct drugs
    //         foreach ($stocks as $stock) {
    //             // Retrieve distinct drugs in the current stock
    //             $distinctDrugs = $stock->stockProducts()
    //                 ->whereHas('product', function ($query) use ($typeProduct) {
    //                     $query->where('type_id', $typeProduct->id); // Assuming 'type_id' is the product type identifier
    //                 })
    //                 ->distinct('product_id') // Ensure uniqueness based on product_id
    //                 ->pluck('product_id'); // Get distinct product IDs

    //             // Merge the distinct product IDs into the array
    //             $distinctProductIds = array_merge($distinctProductIds, $distinctDrugs->toArray());
    //         }

    //         // Count the total number of distinct drugs
    //         $totalDistinctDrugCount = count(array_unique($distinctProductIds));

    //         return response()->json(['total_distinct_drug_count' => $totalDistinctDrugCount], 200);
    //     } catch (\Exception $e) {
    //         return $this->handleException($e);
    //     }
    // }

    // public function countDistinctNotebooksAndCardsInAllStocks()
    // {
    //     try {
    //         // Get the TypeProduct for drugs
    //         $typeProduct = TypeProduct::where('name', 'Notebooks and cards')->first();

    //         if (!$typeProduct) {
    //             return response()->json(['error' => 'TypeProduct not found'], 404);
    //         }

    //         // Retrieve all stocks
    //         $stocks = Stock::all();

    //         // Initialize an array to keep track of distinct product IDs
    //         $distinctProductIds = [];

    //         // Iterate over each stock and count distinct drugs
    //         foreach ($stocks as $stock) {
    //             // Retrieve distinct drugs in the current stock
    //             $distinctDrugs = $stock->stockProducts()
    //                 ->whereHas('product', function ($query) use ($typeProduct) {
    //                     $query->where('type_id', $typeProduct->id); // Assuming 'type_id' is the product type identifier
    //                 })
    //                 ->distinct('product_id') // Ensure uniqueness based on product_id
    //                 ->pluck('product_id'); // Get distinct product IDs

    //             // Merge the distinct product IDs into the array
    //             $distinctProductIds = array_merge($distinctProductIds, $distinctDrugs->toArray());
    //         }

    //         // Count the total number of distinct drugs
    //         $totalDistinctDrugCount = count(array_unique($distinctProductIds));

    //         return response()->json(['total_distinct_drug_count' => $totalDistinctDrugCount], 200);
    //     } catch (\Exception $e) {
    //         return $this->handleException($e);
    //     }
    // }

    // public function getOutOfStockProducts()
    // {
    //     // try {
    //         // Retrieve all stocks
    //         $stocks = Stock::all();

    //         // Initialize an array to store out-of-stock products
    //         $outOfStockProducts = [];

    //         // Iterate over each stock and get out-of-stock products
    //         foreach ($stocks as $stock) {
    //             // Retrieve out-of-stock products in the current stock
    //             $outOfStock = $stock->stockProducts()
    //             ->select('stock_id','product_id', DB::raw('SUM(quantity) as total_quantity'))
    //             ->groupBy('stock_id','product_id')
    //             ->havingRaw('SUM(quantity) <= 0 OR SUM(quantity) IS NULL')
    //             ->with(['product'])
    //             ->get();
    //             // Merge the out-of-stock products into the array
    //             $outOfStockProducts = array_merge($outOfStockProducts, $outOfStock->toArray());
    //         }

    //         // Calculate the count of out-of-stock products
    //         $outOfStockCount = count($outOfStockProducts);

    //         return response()->json(['out_of_stock_products' => $outOfStockProducts, 'out_of_stock_count' => $outOfStockCount], 200);
    //     // } catch (\Exception $e) {
    //     //     return $this->handleException($e);
    //     // }
    // }

    public function getOutOfStockDrugs()
    {
        try {
        // Retrieve all stocks with their associated stock products and products with type "Drugs"
            $stocks = Stock::with(['stockProducts.product.type'])
                ->whereHas('stockProducts.product.type', function ($query) {
                    $query->where('name', 'Drugs');
                })
                ->get();
    
            // Initialize an array to store out-of-stock drugs
            $outOfStockDrugs = [];
    
            // Iterate over each stock
            foreach ($stocks as $stock) {
                // Retrieve out-of-stock drugs in the current stock
                $outOfStock = $stock->stockProducts()
                    ->select('stock_id', 'product_id', DB::raw('SUM(quantity) as total_quantity'))
                    ->groupBy('stock_id', 'product_id')
                    ->havingRaw('SUM(quantity) <= 0 OR SUM(quantity) IS NULL')
                    ->whereHas('product.type', function ($query) {
                        $query->where('name', 'Drugs');
                    })
                    ->with(['product.category'])
                    ->with(['product.sale_unit'])
                    ->with(['product.conditioning_unit'])
                    ->with(['product.administration_route'])
                    ->with(['stock.store'])
                    ->get();
    
                // Merge the out-of-stock drugs into the array
                $outOfStockDrugs = array_merge($outOfStockDrugs, $outOfStock->toArray());
            }
    
            // Calculate the count of out-of-stock drugs
            $outOfStockDrugsCount = count($outOfStockDrugs);

            return response()->json(['out_of_stock_drugs' => $outOfStockDrugs, 'out_of_stock_drugs_count' => $outOfStockDrugsCount], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getOutOfStockConsumables()
    {
        try {
        // Retrieve all stocks with their associated stock products and products with type "Consumables"
            $stocks = Stock::with(['stockProducts.product.type'])
                ->whereHas('stockProducts.product.type', function ($query) {
                    $query->where('name', 'Consumables');
                })
                ->get();
    
            // Initialize an array to store out-of-stock consumables
            $outOfStockConsumables = [];
    
            // Iterate over each stock
            foreach ($stocks as $stock) {
                // Retrieve out-of-stock consumables in the current stock
                $outOfStock = $stock->stockProducts()
                    ->select('stock_id', 'product_id', DB::raw('SUM(quantity) as total_quantity'))
                    ->groupBy('stock_id', 'product_id')
                    ->havingRaw('SUM(quantity) <= 0 OR SUM(quantity) IS NULL')
                    ->whereHas('product.type', function ($query) {
                        $query->where('name', 'Consumables');
                    })
                    ->with(['product.category'])
                    ->with(['product.sale_unit'])
                    ->with(['product.conditioning_unit'])
                    ->with(['product.administration_route'])
                    ->with(['stock.store'])
                    ->get();
    
                // Merge the out-of-stock consumables into the array
                $outOfStockConsumables = array_merge($outOfStockConsumables, $outOfStock->toArray());
            }
    
            // Calculate the count of out-of-stock consumables
            $outOfStockConsumablesCount = count($outOfStockConsumables);
    
            return response()->json(['out_of_stock_consumables' => $outOfStockConsumables, 'out_of_stock_consumables_count' => $outOfStockConsumablesCount], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getOutOfStockNotebooksAndCards()
    {
        try {
        // Retrieve all stocks with their associated stock products and products with type "Notebooks and cards"
            $stocks = Stock::with(['stockProducts.product.type'])
                ->whereHas('stockProducts.product.type', function ($query) {
                    $query->where('name', 'Notebooks and cards');
                })
                ->get();
    
            // Initialize an array to store out-of-stock notebooksAndCards
            $outOfStockNotebooksAndCards = [];
    
            // Iterate over each stock
            foreach ($stocks as $stock) {
                // Retrieve out-of-stock notebooksAndCards in the current stock
                $outOfStock = $stock->stockProducts()
                    ->select('stock_id', 'product_id', DB::raw('SUM(quantity) as total_quantity'))
                    ->groupBy('stock_id', 'product_id')
                    ->havingRaw('SUM(quantity) <= 0 OR SUM(quantity) IS NULL')
                    ->whereHas('product.type', function ($query) {
                        $query->where('name', 'Notebooks and cards');
                    })
                    ->with(['product.category'])
                    ->with(['product.sale_unit'])
                    ->with(['product.conditioning_unit'])
                    ->with(['product.administration_route'])
                    ->with(['stock.store'])
                    ->get();
    
                // Merge the out-of-stock notebooksAndCards into the array
                $outOfStockNotebooksAndCards = array_merge($outOfStockNotebooksAndCards, $outOfStock->toArray());
            }
    
            // Calculate the count of out-of-stock notebooksAndCards
            $outOfStockNotebooksAndCardsCount = count($outOfStockNotebooksAndCards);
    
            return response()->json(['out_of_stock_notebooks_and_cards' => $outOfStockNotebooksAndCards, 'out_of_stock_notebooks_and_cards_count' => $outOfStockNotebooksAndCardsCount], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function getExpiredDrugs()
    {
        try {
            // Retrieve all stocks with their associated stock products and products with type "Drugs"
            $stocks = Stock::with(['stockProducts.product'])
                ->whereHas('stockProducts.product.type', function ($query) {
                    $query->where('name', 'Drugs');
                })
                ->get();
    
            // Initialize an array to store expired drugs
            $expiredDrugs = [];
    
            // Iterate over each stock
            foreach ($stocks as $stock) {
                // Retrieve expired drugs in the current stock
                $expired = $stock->stockProducts()
                    ->where('expire_date', '<', now()) // Adjusted for expire_date
                    ->where('quantity', '>', 0) // Exclude lines where the quantity is zero
                    ->with(['product.category'])
                    ->with(['product.sale_unit'])
                    ->with(['product.conditioning_unit'])
                    ->with(['product.administration_route'])
                    ->with(['stock.store'])
                    ->get(['uuid', 'lot_number', 'product_id', 'quantity', 'expire_date']); // Only select necessary columns
    
                // Merge the expired drugs into the array
                $expiredDrugs = array_merge($expiredDrugs, $expired->toArray());
            }
    
            // Calculate the count of expired drugs
            $expiredDrugsCount = count($expiredDrugs);
    
            return response()->json(['expired_drugs' => $expiredDrugs, 'expired_drugs_count' => $expiredDrugsCount], 200);
    
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function countProductsForPharmacySale()
    {
        try {
            // Retrieve all stocks for pharmacy sale
            $stocks = Stock::where('for_pharmacy_sale', 1)->get();
    
            // Initialize arrays to keep track of distinct product IDs and their quantities
            $distinctDrugsInfo = [];
            $totalQuantityAllProducts = 0;
    
            // Iterate over each stock and count distinct drugs
            foreach ($stocks as $stock) {
                // Retrieve distinct drugs in the current stock for pharmacy sale
                $distinctDrugs = $stock->stockProducts()
                    ->distinct('product_id') // Ensure uniqueness based on product_id
                    ->pluck('product_id'); // Get distinct product IDs
    
                // Iterate over distinct drugs and sum their quantities
                foreach ($distinctDrugs as $productId) {
                    $totalQuantity = $stock->stockProducts()
                        ->where('product_id', $productId)
                        ->sum('quantity');
    
                    // Add the information to the array
                    $distinctDrugsInfo[$productId] = $totalQuantity;
    
                    // Add the quantity to the total quantity of all products
                    $totalQuantityAllProducts += $totalQuantity;
                }
            }
    
            // return response()->json(['distinct_drugs_info' => $distinctDrugsInfo, 'total_quantity_all_products' => $totalQuantityAllProducts], 200);
            return response()->json(['total_quantity_all_products' => $totalQuantityAllProducts], 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
