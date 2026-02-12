<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepositoryEloquent;
use Modules\Stock\Http\Resources\StockResource;
use Modules\Stock\Http\Resources\StocksResource;
use Modules\Stock\Http\Requests\StockIndexRequest;
use Modules\Stock\Http\Requests\StockStoreRequest;
use Modules\Stock\Http\Requests\StockDeleteRequest;
use Modules\Stock\Http\Requests\StockUpdateRequest;
use Modules\Stock\Http\Controllers\StockController as BaseStockController;
use Modules\Stock\Repositories\StockRepositoryEloquent;
use Modules\Stock\Repositories\StoreRepositoryEloquent;

class StockController extends BaseStockController {

     /**
     * @var StockRepositoryEloquent
     */
    protected $stockRepositoryEloquent;

    /**
     * @var UserRepositoryEloquent
     */
    protected $userRepositoryEloquent;

    /**
     * @var StoreRepositoryEloquent
     */
    protected $storeRepositoryEloquent;

    public function __construct(StockRepositoryEloquent $stockRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent, StoreRepositoryEloquent $storeRepositoryEloquent) {
        parent::__construct();
        $this->stockRepositoryEloquent = $stockRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
        $this->storeRepositoryEloquent = $storeRepositoryEloquent;
    }
    
      /**
     * Return a listing of the resource.
     * @param StockIndexRequest $request
     * @return StocksResource
     */
    public function index(StockIndexRequest $request)
    {
        $donnees = $this->stockRepositoryEloquent->paginate($this->nombrePage);   
        return new StocksResource($donnees);
    }

    /**
     * Show the specified resource.
     * @param StockIndexRequest $request
     * @param string $uuid
     * @return StockResource
     */ 
    public function show(StockIndexRequest $request, $uuid) {
        try {
            $item = $this->stockRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return response()->json(['message' => 'Stock non trouvée'], 404);
            }
            return new StockResource($item);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param StockStoreRequest $request
     * @return StockResource
     */
    public function store(StockStoreRequest $request)
    {
        $attributs = $request->all();

        //This block must be uncommented when the user model will be created and binded
        $item = DB::transaction(function () use ($attributs) {
        //     $user = $this->userRepositoryEloquent->findByUuid($attributs['user_id'])->first();
        //     $attributs['user_id'] = $user->id;
        
            //This line must be deleted when the user model will be created and binded
            $attributs['user_id'] = 1;

            $store = $this->storeRepositoryEloquent->findByUuid($attributs['store_id'])->first();
            $attributs['store_id'] = $store->id;

            $item = $this->stockRepositoryEloquent->create($attributs);
            return $item;
        });

        $item = $item->fresh();

        return new StockResource($item);
    }
    
    /**
     * Update the specified resource in storage.
     * @param StockUpdateRequest $request
     * @return StockResource
     */
    public function update(StockUpdateRequest $request, $uuid)
    {
        $item = $this->stockRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        // $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
        // $attributs['users_id'] = $user->id;
        
        $store = $this->storeRepositoryEloquent->findByUuid($attributs['store_id'])->first();
        $attributs['store_id'] = $store->id;

        $item = $this->stockRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();

        return new StockResource($item);
    }

    /**
     * Remove the specified resource from storage.
     * @param StockDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(StockDeleteRequest $request, $uuid)
    {
        $stock = $this->stockRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        
        //Implement deleting conditions
        if($stock->stockProducts->count()>0){
            $data = [
                "message" => __("Impossible de supprimer ce stock ! Il contient au moins un produit."),
            ];

            return reponse_json_transform($data, 400);
        }
        // elseif($stock->stockTransfers->count()>0){
        //     $data = [
        //         "message" => __("Impossible de supprimer ce stock ! Il est lié à au moins un transfert."),
        //     ];

        //     return reponse_json_transform($data, 400);
        // }
        elseif($stock->supplies->count()>0){
            $data = [
                "message" => __("Impossible de supprimer ce stock ! Il est lié à au moins un approvisionnement."),
            ];

            return reponse_json_transform($data, 400);
        }
        else
        {
            $this->stockRepositoryEloquent->delete($stock->id);
        
            $data = [
                "message" => __("Stock supprimé avec succès !"),
            ];
            return reponse_json_transform($data);
        }  
    }    

}
