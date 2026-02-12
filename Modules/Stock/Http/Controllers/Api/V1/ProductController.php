<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Entities\Product;
use Illuminate\Support\Facades\Auth;
use Modules\Stock\Entities\StockProduct;
use App\Repositories\UserRepositoryEloquent;
use Modules\Stock\Http\Resources\ProductResource;
use Modules\Stock\Http\Resources\ProductsResource;
use Modules\Stock\Http\Controllers\StockController;
use Modules\Stock\Http\Requests\ProductIndexRequest;
use Modules\Stock\Http\Requests\ProductStoreRequest;
use Modules\Stock\Http\Requests\ProductDeleteRequest;
use Modules\Stock\Http\Requests\ProductUpdateRequest;
use Modules\Stock\Http\Resources\StockProductsResource;
use Modules\Stock\Http\Resources\SupplyProductResource;
use Modules\Stock\Http\Resources\SupplyProductsResource;
use Modules\Stock\Repositories\ProductRepositoryEloquent;
use Modules\Stock\Repositories\CategoryRepositoryEloquent;
use Modules\Stock\Repositories\SaleUnitRepositoryEloquent;
use Modules\Stock\Repositories\TypeProductRepositoryEloquent;
use Modules\Stock\Repositories\ConditioningUnitRepositoryEloquent;
use Modules\Stock\Repositories\AdministrationRouteRepositoryEloquent;

class ProductController extends StockController {

    /**
     * @var ProductRepositoryEloquent
     */
    protected $productRepositoryEloquent;
    
    /**
     * @var ConditioningUnitRepositoryEloquent
     */
    protected $conditioningUnitRepositoryEloquent;

    /**
     * @var AdministrationRouteRepositoryEloquent
     */
    protected $administrationRouteRepositoryEloquent;

    /**
     * @var SaleUnitRepositoryEloquent
     */
    protected $saleUnitRepositoryEloquent;

    /**
     * @var UserRepositoryEloquent
     */
    protected $userRepositoryEloquent;

    /**
     * @var CategoryRepositoryEloquent
     */
    protected $categoryRepositoryEloquent;

    /**
     * @var TypeProductRepositoryEloquent
     */
    protected $typeProductRepositoryEloquent;

    public function __construct(ProductRepositoryEloquent $productRepositoryEloquent, 
    UserRepositoryEloquent $userRepositoryEloquent, 
    ConditioningUnitRepositoryEloquent $conditioningUnitRepositoryEloquent, 
    AdministrationRouteRepositoryEloquent $administrationRouteRepositoryEloquent, 
    SaleUnitRepositoryEloquent $saleUnitRepositoryEloquent, 
    CategoryRepositoryEloquent $categoryRepositoryEloquent,
    TypeProductRepositoryEloquent $typeProductRepositoryEloquent) {
        parent::__construct();
        $this->productRepositoryEloquent = $productRepositoryEloquent;
        $this->conditioningUnitRepositoryEloquent = $conditioningUnitRepositoryEloquent;
        $this->administrationRouteRepositoryEloquent = $administrationRouteRepositoryEloquent;
        $this->saleUnitRepositoryEloquent = $saleUnitRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
        $this->categoryRepositoryEloquent = $categoryRepositoryEloquent;
        $this->typeProductRepositoryEloquent = $typeProductRepositoryEloquent;
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
                return reponse_json_transform(['message' => 'Produit non trouvée'], 404);
            }
            return new ProductResource($item);
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
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

        $item = DB::transaction(function () use ($attributs) {
            // Get the authentified user id
            $user = Auth::user();
            if (!$user) {
                throw new \Exception('Utilisateur non authentifié');
            }
            $attributs['user_id'] = $user->id;

            // Get the conditioning unit id
            $conditioningUnit = $this->conditioningUnitRepositoryEloquent->findByUuid($attributs['conditioning_unit_id'])->first();
            $attributs['conditioning_unit_id'] = $conditioningUnit->id;

            // Get the administration route id
            if (isset($attributs['administration_route_id'])) {
                $administrationRoute = $this->administrationRouteRepositoryEloquent->findByUuid($attributs['administration_route_id'])->first();
                $attributs['administration_route_id'] = $administrationRoute->id;
            }

            // Get the sale unit id
            $saleUnit = $this->saleUnitRepositoryEloquent->findByUuid($attributs['sale_unit_id'])->first();
            $attributs['sale_unit_id'] = $saleUnit->id;

            // Get the category id

            $category = $this->categoryRepositoryEloquent->findByUuid($attributs['category_id'])->first();
            $attributs['category_id'] = $category->id;

            // Get the type of the product id by its name
            $typeProduct = $this->typeProductRepositoryEloquent->findByField('name', $attributs['type_name'])->first();
            $attributs['type_id'] = $typeProduct->id;


            //Generate the product code based on:
            //The three first letters of the $typeProduct->name followed by an hyphen
            //Then the three first letters of the $category->name followed by an hyphen
            //Then followed by five random number
            $unaccentedStringTypeProduct = strtr($typeProduct->name, [
                'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
                'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
                'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
                'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
                'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
                'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
                'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
                'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
                'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
                'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
                'Ý' => 'Y', 'ý' => 'y',
            ]);
            $unaccentedStringcategory = strtr($category->name, [
                'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
                'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
                'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
                'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
                'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
                'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
                'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
                'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
                'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
                'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
                'Ý' => 'Y', 'ý' => 'y',
            ]);
            $typeProductCode = substr($unaccentedStringTypeProduct, 0, 3);
            $categoryCode = substr($unaccentedStringcategory, 0, 3);


            // Define a variable to store the generated code
            $generatedCode = '';

            do {
                // Generate a random 5-digit number and pad it with leading zeros
                $randomNumbers = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

                // Construct the code
                $generatedCode = strtoupper($typeProductCode) . '-' . strtoupper($categoryCode) . '-' . $randomNumbers;

                // Check if a product with this code already exists
                $existingCode = $this->productRepositoryEloquent->findByCode($generatedCode);

            } while ($existingCode);

            // Add the generated code to the attributs
            $attributs['code'] = $generatedCode;

            $item = $this->productRepositoryEloquent->create($attributs);
            return $item;
        });

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
        //Check if the product exist and return an error if not.
        try {
            $item = $this->productRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return reponse_json_transform(['message' => 'Produit non trouvé'], 404);
            }
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }

        $attributs = $request->all();

        // Get the authentified user id
        $user = Auth::user();
        $attributs['user_id'] = $user->id;

        // Get the conditioning unit id
        $conditioningUnit = $this->conditioningUnitRepositoryEloquent->findByUuid($attributs['conditioning_unit_id'])->first();
        $attributs['conditioning_unit_id'] = $conditioningUnit->id;

        // Get the administration route id
        if (isset($attributs['administration_route_id'])) {
            $administrationRoute = $this->administrationRouteRepositoryEloquent->findByUuid($attributs['administration_route_id'])->first();
            $attributs['administration_route_id'] = $administrationRoute->id;
        }
        

        // Get the sale unit id
        $saleUnit = $this->saleUnitRepositoryEloquent->findByUuid($attributs['sale_unit_id'])->first();
        $attributs['sale_unit_id'] = $saleUnit->id;

        // Get the category id
        $category = $this->categoryRepositoryEloquent->findByUuid($attributs['category_id'])->first();
        $attributs['category_id'] = $category->id;

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


    //Retrieve from the database all drugs
    public function getDrugs()
    {
        $typeProduct = $this->typeProductRepositoryEloquent->findByField('name', "Drugs")->first();
     
        // Retrieve all supplies related to the store
        $products = Product::where('type_id', $typeProduct->id )->get();

        return new ProductsResource($products);
    }
    //Retrieve from the database all drugs
    public function getAllConsumables()
    {
        $typeProduct = $this->typeProductRepositoryEloquent->findByField('name', "Consumables")->first();
     
        // Retrieve all supplies related to the store
        $products = Product::where('type_id', $typeProduct->id )->get();

        return new ProductsResource($products);
    }
    //Retrieve from the database all drugs
    public function getNotebooksAndCards()
    {
        $typeProduct = $this->typeProductRepositoryEloquent->findByField('name', "Notebooks and cards")->first();
     
        // Retrieve all supplies related to the store
        $products = Product::where('type_id', $typeProduct->id )->get();

        return new ProductsResource($products);
    }

    public function getProductLocations($uuid)
    {
        $product = $this->productRepositoryEloquent->findByUuid($uuid)->first();

        // Utilisez le modèle StockProduct pour récupérer tous les endroits du produit
        $productLocations = StockProduct::where('product_id', $product->id)
        ->get();

        return new StockProductsResource($productLocations);
    }

    public function getProductFormatted($typeIdentifiant, $identifiant)
    {
        if (!isset($typeIdentifiant) || !in_array($typeIdentifiant, ['code', 'uuid', 'id'])) {
            $data = [
                "message" => __("Type identifiant incorrect"),
            ];
            return reponse_json_transform($data, 400);
        }
    
        if (!isset($identifiant)) {
            $data = [
                "message" => __("Identifiant manquant"),
            ];
            return reponse_json_transform($data, 400);
        }

        $product = null;

        switch ($typeIdentifiant) {
            case 'uuid':
                $product = $this->productRepositoryEloquent->findByUuid($identifiant)->first();
                break;
            case 'id':
                $product = $this->productRepositoryEloquent->find($identifiant);
                break;
            case 'code':
                $product = $this->productRepositoryEloquent->findByCode($identifiant)->first();
                break;
        }

        if ($product) {
            $conditioningUnit = $this->conditioningUnitRepositoryEloquent->find($product->conditioning_unit_id);
            $saleUnit = $this->saleUnitRepositoryEloquent->find($product->sale_unit_id);

            $productFormatted = $product->name . ' ' . $conditioningUnit->name . ' ' . $saleUnit->name;
            return reponse_json_transform($productFormatted, 200);
        } else {
            $data = [
                "message" => __("Produit non trouvé"),
            ];
            return reponse_json_transform($data, 404);
        }
    }

    public function getAllSupplyProducts($uuid)
    {
        $product = Product::where('uuid',$uuid)->first();
        return $product;

        if ($product) {
            // Access the supplyProducts relationship directly without instantiation
            $allSupplyProducts = $product->supplyProducts;

            return $allSupplyProducts;
        }

        // Handle the case where the product is not found
        // For example, you might want to return an error response
        return response()->json(['error' => 'Product not found'], 404);
    }
}
