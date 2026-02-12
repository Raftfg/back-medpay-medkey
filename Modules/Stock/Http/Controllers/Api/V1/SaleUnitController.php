<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Repositories\UserRepositoryEloquent;
use Modules\Stock\Http\Resources\SaleUnitResource;
use Modules\Stock\Http\Controllers\StockController;
use Modules\Stock\Http\Resources\SaleUnitsResource;
use Modules\Stock\Http\Requests\SaleUnitIndexRequest;
use Modules\Stock\Http\Requests\SaleUnitStoreRequest;
use Modules\Stock\Http\Requests\SaleUnitDeleteRequest;
use Modules\Stock\Http\Requests\SaleUnitUpdateRequest;
use Modules\Stock\Repositories\SaleUnitRepositoryEloquent;

class SaleUnitController extends StockController {

     /**
     * @var SaleUnitRepositoryEloquent
     */
    protected $saleUnitRepositoryEloquent;

    /**
     * @var UserRepositoryEloquent
     */
    protected $userRepositoryEloquent;

    public function __construct(SaleUnitRepositoryEloquent $saleUnitRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent) {
        parent::__construct();
        $this->saleUnitRepositoryEloquent = $saleUnitRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }
    
    /**
     * Return a listing of the resource.
     * @param SaleUnitIndexRequest $request
     * @return SaleUnitsResource
     */
    public function index(SaleUnitIndexRequest $request)
    {
        $donnees = $this->saleUnitRepositoryEloquent->paginate($this->nombrePage);   
        return new SaleUnitsResource($donnees);
    }

    /**
     * Show the specified resource.
     * @param SaleUnitIndexRequest $request
     * @param string $uuid
     * @return SaleUnitResource
     */ 
    public function show(SaleUnitIndexRequest $request, $uuid) {
        try {
            $item = $this->saleUnitRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return reponse_json_transform(['message' => 'Unité de vente non trouvée'], 404);
            }
            return new SaleUnitResource($item);
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param SaleUnitStoreRequest $request
     * @return SaleUnitResource
     */
    public function store(SaleUnitStoreRequest $request)
    {
        $attributs = $request->all();

        $item = DB::transaction(function () use ($attributs) {
            // Get the authentified user id
            $user = Auth::user();
            $attributs['user_id'] = $user->id;
        
            $item = $this->saleUnitRepositoryEloquent->create($attributs);
            return $item;
        });

        $item = $item->fresh();

        return new SaleUnitResource($item);
    }
    
    /**
     * Update the specified resource in storage.
     * @param SaleUnitUpdateRequest $request
     * @return SaleUnitResource
     */
    public function update(SaleUnitUpdateRequest $request, $uuid)
    {
        //Check if the sale unit exist and return an error if not.
        try {
            $item = $this->saleUnitRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return reponse_json_transform(['message' => 'Unité de vente non trouvée'], 404);
            }
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }

        $attributs = $request->all();

        // Get the authentified user id
        $user = Auth::user();
        $attributs['user_id'] = $user->id;

        $item = $this->saleUnitRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();

        return new SaleUnitResource($item);
    }

    /**
     * Remove the specified resource from storage.
     * @param SaleUnitDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(SaleUnitDeleteRequest $request, $uuid)
    {
        //Check if the sale unit exist and return an error if not.
        try {
            $saleUnit = $this->saleUnitRepositoryEloquent->findByUuid($uuid)->first();
            if (!$saleUnit) {
                return reponse_json_transform(['message' => 'Unité de vente non trouvée'], 404);
            }
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }

        //Implement deleting conditions
        if($saleUnit->products->count()>0){
            $data = [
                "message" => __("Impossible de supprimer cette unité de vente ! Elle est liée à au moins un produit."),
            ];

            return reponse_json_transform($data, 400);
        }
        else
        {
            $this->saleUnitRepositoryEloquent->delete($saleUnit->id);
        
            $data = [
                "message" => __("Unité de vente supprimée avec succès !"),
            ];
            return reponse_json_transform($data);
        }  
    }    
}
