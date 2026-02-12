<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Stock\Entities\AdministrationRoute;
use Modules\Stock\Entities\StockTransferProduct;
// use Modules\Stock\Entities\ConditioningUnit;
// use Modules\Stock\Entities\SaleUnit;
use Modules\Stock\Http\Controllers\Api\V1\PharmacyController;
use Modules\Stock\Http\Controllers\Api\V1\ConditioningUnitController;
use Modules\Stock\Http\Controllers\Api\V1\AdministrationRouteController;
use Modules\Stock\Http\Controllers\Api\V1\SaleUnitController;
use Modules\Stock\Http\Controllers\Api\V1\ProductController;
use Modules\Stock\Http\Controllers\Api\V1\StoreController;
use Modules\Stock\Http\Controllers\Api\V1\StockController;
use Modules\Stock\Http\Controllers\Api\V1\TypeProductController;
use Modules\Stock\Http\Controllers\Api\V1\CategoryController;
use Modules\Stock\Http\Controllers\Api\V1\DestockController;
use Modules\Stock\Http\Controllers\Api\V1\SupplierController;
use Modules\Stock\Http\Controllers\Api\V1\SupplyController;
use Modules\Stock\Http\Controllers\Api\V1\SupplyProductController;
use Modules\Stock\Http\Controllers\Api\V1\StockProductController;
use Modules\Stock\Http\Controllers\Api\V1\StockTransferController;
use Modules\Stock\Http\Controllers\Api\V1\StockTransferProductContoller;

/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | is assigned the "api" middleware group. Enjoy building your API!
  |
 */

// $middlewareBase = tenants_middleware();
// Route::group(['middleware' => $middlewareBase,], function () use ($middlewareBase) {
    Route::group(['prefix' => 'api'], function () { //CL - Pour garder le même prefixe que les autres routes
        $apiVersion = 'v' . config('premier.api_version');
        Route::group(['prefix' => $apiVersion], function () {  //CL - Pour garder le même prefixe que les autres routes

            


            Route::group(['middleware' => ['auth:api']], function () {
                Route::apiResource('conditioning_units', ConditioningUnitController::class);
                Route::apiResource('sale_units', SaleUnitController::class);
                Route::apiResource('administration_routes', AdministrationRouteController  ::class);
                Route::apiResource('products', ProductController::class);
                Route::apiResource('stores', StoreController::class);
                Route::apiResource('stocks', StockController::class);
                Route::apiResource('type_products', TypeProductController::class);
                Route::apiResource('categories', CategoryController::class);
                Route::apiResource('suppliers', SupplierController::class);
                Route::apiResource('supplies', SupplyController::class);
                Route::apiResource('supply_products', SupplyProductController::class);
                Route::apiResource('stock_products', StockProductController::class);
                Route::apiResource('stock_transfers', StockTransferController::class);
                Route::apiResource('stock_transfer_products', StockTransferProductContoller::class);
                Route::apiResource('destocks', DestockController::class);

                //Products
                Route::get('/drugs/available', [StockProductController::class, 'getAllDrugsAvailable']);
                Route::get('/product/drugs', [ProductController::class, 'getDrugs']);
                Route::get('/product/consumables', [ProductController::class, 'getAllConsumables']);
                Route::get('/product/notebooks_and_cards', [ProductController::class, 'getNotebooksAndCards']);
                Route::get('/product/{uuid}/locations', [ProductController::class, 'getProductLocations']);
                Route::get('/product/{uuid}/supply-products', [ProductController::class, 'getAllSupplyProducts']);
                Route::get('/product-formatted/{typeIdentifiant}/{identifiant}', [ProductController::class, 'getProductFormatted']);
                
                Route::get('/product/{productIdentifier}/quantity-in-stock/{stockUuid?}', [StockProductController::class, 'getProductQuantity']);
                Route::get('/product/lot/{lotIdentifier}/details', [StockProductController::class, 'getProductLotDetailsByStockProductService']);
                Route::get('/product/{productIdentifier}/destock/{quantityToRetrieve}/in-stock/{stockUuid?}', [StockProductController::class, 'destockProduct']);
                Route::post('/destock/in-stock', [StockProductController::class, 'destockProducts']);

                // Reports
                Route::get('/stock-products/count-distinct-drugs-in-all-stocks', [StockProductController::class, 'countDistinctDrugsInAllStocks']);
                Route::get('/stock-products/count-distinct-consumables-in-all-stocks', [StockProductController::class, 'countDistinctConsumablesInAllStocks']);
                Route::get('/stock-products/count-distinct-notebooks-and-cards-in-all-stocks', [StockProductController::class, 'countDistinctNotebooksAndCardsInAllStocks']);

                Route::get('/stock-products/get-out-of-stock-drugs', [StockProductController::class, 'getOutOfStockDrugs']);
                Route::get('/stock-products/get-out-of-stock-consumables', [StockProductController::class, 'getOutOfStockConsumables']);
                Route::get('/stock-products/get-out-of-stock-notebook-and-cards', [StockProductController::class, 'getOutOfStockNotebooksAndCards']);

                Route::get('/stock-products/get-expire-drugs', [StockProductController::class, 'getExpiredDrugs']);

                Route::get('/stock-pharmacie/count-products', [StockProductController::class, 'countProductsForPharmacySale']);


            
                // Route::get('/destock/{quantity}/from-product-lot/{lotUuid}', [StockProductController::class, 'destockProductByLotUuid']);
                Route::post('/destock/products/by-lot-uuid', [StockProductController::class, 'destockProductsByLotUuid']);

                //StockProducts
                Route::get('/stock/{uuid}/drugs', [StockProductController::class, 'getDrugsInStock']);
                Route::get('/drugs-available', [StockProductController::class, 'getAllDrugsAvailableInAllStocks']);
                Route::get('/stock/{uuid}/consumables', [StockProductController::class, 'getConsumablesInStock']);
                Route::get('/consumables-available', [StockProductController::class, 'getAllConsumablesAvailableInAllStocks']);
                Route::get('/stock/{uuid}/notebooks_and_cards', [StockProductController::class, 'getNotebooksAndCardsInStock']);
                Route::get('/notebooks-available', [StockProductController::class, 'getAllNotebooksAvailableInAllStocks']);
                Route::get('/stock/{uuid}/products', [StockProductController::class, 'getProductsInStock']);
                Route::get('/product/{uuid}/lots/in-stock/{stockUuid?}', [StockProductController::class, 'getProductLotsInFIFOOrderInStockService']);
                Route::get('/stock/{stockUuid?}/products-available', [StockProductController::class, 'getAllProductsInStockService']);

                //Supply
                Route::get('/supply/{uuid}/products', [SupplyController::class, 'getProducts']);

                //StockTransfer
                Route::get('/stockTransfer/{uuid}/products', [StockTransferController::class, 'getProducts']);

                //Store
                Route::get('/store/{uuid}/stocks', [StoreController::class, 'getStocks']);
                Route::get('/store/{uuid}/supplies', [StoreController::class, 'getSupplies']);
                Route::get('/store/{uuid}/stock_transfers', [StoreController::class, 'getStockTransfers']);
            
                Route::get('/type_product/{uuid}/categories', [TypeProductController::class, 'getCategories']);

                //Pharmacy
                //Change must be made to uuid
                Route::get('/movement/{id}/products', [PharmacyController::class, 'getProductsForMovement']);
                Route::post('/destock/products/', [PharmacyController::class, 'destock']);
                    
                
                //Destock
                Route::get('/user-destocked-product-on-date', [DestockController::class, 'getDestockedProductsByUserOnADate']);
                Route::get('/users-destockers', [DestockController::class, 'getDestockersUsers']);
            });
        });
    });
// });
