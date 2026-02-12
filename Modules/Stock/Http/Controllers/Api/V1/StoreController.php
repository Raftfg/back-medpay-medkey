<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use DateTime;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Modules\Stock\Entities\Stock;
use Modules\Stock\Entities\Store;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Entities\Supply;
use Modules\Stock\Entities\StockTransfer;
use App\Repositories\UserRepositoryEloquent;
use Modules\Stock\Http\Resources\StoreResource;
use Modules\Stock\Http\Resources\StocksResource;
use Modules\Stock\Http\Resources\StoresResource;
use Modules\Stock\Http\Requests\StoreIndexRequest;
use Modules\Stock\Http\Requests\StoreStoreRequest;
use Modules\Stock\Http\Resources\SuppliesResource;
use Modules\Stock\Http\Controllers\StockController;
use Modules\Stock\Http\Requests\StoreDeleteRequest;
use Modules\Stock\Http\Requests\StoreUpdateRequest;
use Modules\Stock\Repositories\StockRepositoryEloquent;
use Modules\Stock\Repositories\StoreRepositoryEloquent;
use Modules\Stock\Http\Resources\StockTransfersResource;

class StoreController extends StockController {

    /**
     * @var StoreRepositoryEloquent
     */
    protected $storeRepositoryEloquent;

    /**
     * @var StoreRepositoryEloquent
     */
    protected $stockRepositoryEloquent;

    /**
     * @var UserRepositoryEloquent
     */
    protected $userRepositoryEloquent;

    public function __construct(StoreRepositoryEloquent $storeRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent, StockRepositoryEloquent $stockRepositoryEloquent) {
        parent::__construct();
        $this->storeRepositoryEloquent = $storeRepositoryEloquent;
        $this->stockRepositoryEloquent = $stockRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }
    
    /**
     * Return a listing of the resource.
     * @param StoreIndexRequest $request
     * @return StoresResource
     */
    public function index(StoreIndexRequest $request)
    {
        $donnees = $this->storeRepositoryEloquent->paginate($this->nombrePage);
        return new StoresResource($donnees);
    }

    /**
     * Show the specified resource.
     * @param StoreIndexRequest $request
     * @param string $uuid
     * @return StoreResource
     */ 
    public function show(StoreIndexRequest $request, $uuid) {
        try {
            $item = $this->storeRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return response()->json(['message' => 'Magasin non trouvé'], 404);
            }
            return new StoreResource($item);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param StoreStoreRequest $request
     * @return StoreResource
     */
    public function store(StoreStoreRequest $request)
    {
        $attributs = $request->all();

        //This block must be uncommented when the user model will be created and binded
        // $item = DB::transaction(function () use ($attributs) {
        //     $user = $this->userRepositoryEloquent->findByUuid($attributs['user_id'])->first();
        //     $attributs['user_id'] = $user->id;

        //     $item = $this->storeRepositoryEloquent->create($attributs);
        //     return $item;
        // });

        do {
            // Generate a random 5-digit number and pad it with leading zeros
            $randomNumbers = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

            // Construct the code
            $generatedCode = "MAG" . '-' . $randomNumbers;

            // Check if a store with this code already exists
            $existingCode = $this->storeRepositoryEloquent->findByCode($generatedCode);

        } while ($existingCode);

        // Add the generated code to the attributs
        $attributs['code'] = $generatedCode;

        //This line must be deleted when the user model will be created and binded
        $attributs['user_id'] = 1;

        $item = $this->storeRepositoryEloquent->create($attributs);
        $item = $item->fresh();

        return new StoreResource($item);
    }
    
    /**
     * Update the specified resource in storage.
     * @param StoreUpdateRequest $request
     * @return StoreResource
     */
    public function update(StoreUpdateRequest $request, $uuid)
    {
        $item = $this->storeRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        // $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
        // $attributs['users_id'] = $user->id;

        $attributs['code'] = $item->code;

        $item = $this->storeRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();

        return new StoreResource($item);
    }

    /**
     * Remove the specified resource from storage.
     * @param StoreDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(StoreDeleteRequest $request, $uuid)
    {
        $store = $this->storeRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?

        //Implement deleting conditions
        if($store->stocks->count()>0){
            $data = [
                "message" => __("Impossible de supprimer ce magasin ! Il est lié à au moins un stock."),
            ];

            return reponse_json_transform($data, 400);
        }
        else
        {
            $this->storeRepositoryEloquent->delete($store->id);
        
            $data = [
                "message" => __("Magasin supprimé avec succès !"),
            ];
            return reponse_json_transform($data);
        }        
    }    

    public function getStocks($uuid)
    {
        $store = $this->storeRepositoryEloquent->findByUuid($uuid)->first();
        $storeId = $store->id;

        $stocks = $this->stockRepositoryEloquent->findByField('store_id', $storeId);
        
        return reponse_json_transform($stocks);
    }

    public function getSupplies($uuid)
    {
        //Try to retrieve the store
        $store = $this->storeRepositoryEloquent->findByUuid($uuid)->first();
        //Return an error if the store hasn't been found
        if (!$store) {
            return response()->json(['message' => 'Magasin non trouvé'], 404);
        }
        //Get the store with Eloquent
        $store = Store::find($store->id);

        //Get all supplies linked with this store
        $supplies = Supply::whereIn('stock_id', $store->stocks->pluck('id'))->get();
        $supplies->load('stock');

        return new SuppliesResource($supplies);
    }

    public function getStockTransfers($uuid)
    {
        //Try to retrieve the store
        $store = $this->storeRepositoryEloquent->findByUuid($uuid)->first();
        //Return an error if the store hasn't been found
        if (!$store) {
            return response()->json(['message' => 'Magasin non trouvé'], 404);
        }
        //Get the store with Eloquent
        $store = Store::find($store->id);

        //Get all supplies linked with this store
        $stockTransfers = StockTransfer::whereIn('from_stock_id', $store->stocks->pluck('id'))->get();
        $stockTransfers->load('fromStock');

        return new StockTransfersResource($stockTransfers);
    }
}
