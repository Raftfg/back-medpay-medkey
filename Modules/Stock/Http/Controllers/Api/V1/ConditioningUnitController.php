<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Repositories\UserRepositoryEloquent;
use Modules\Stock\Entities\ConditioningUnit;
use Symfony\Component\HttpFoundation\Request;
use Modules\Stock\Http\Controllers\StockController;
use Modules\Stock\Http\Resources\ConditioningUnitResource;
use Modules\Stock\Http\Resources\ConditioningUnitsResource;
use Modules\Stock\Http\Requests\ConditioningUnitIndexRequest;
use Modules\Stock\Http\Requests\ConditioningUnitStoreRequest;
use Modules\Stock\Http\Requests\ConditioningUnitDeleteRequest;
use Modules\Stock\Http\Requests\ConditioningUnitUpdateRequest;
use Modules\Stock\Repositories\ConditioningUnitRepositoryEloquent;

class ConditioningUnitController extends StockController {

     /**
     * @var ConditioningUnitRepositoryEloquent
     */
    protected $conditioningUnitRepositoryEloquent;

    /**
     * @var UserRepositoryEloquent
     */
    protected $userRepositoryEloquent;

    public function __construct(ConditioningUnitRepositoryEloquent $conditioningUnitRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent) {
        parent::__construct();
        $this->conditioningUnitRepositoryEloquent = $conditioningUnitRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }
    
    /**
     * Return a listing of the resource.
     * @param ConditioningUnitIndexRequest $request
     * @return ConditioningUnitsResource
     */
    public function index(ConditioningUnitIndexRequest $request)
    {
        $donnees = $this->conditioningUnitRepositoryEloquent->paginate($this->nombrePage);   
        return new ConditioningUnitsResource($donnees);
    }

    /**
     * Show the specified resource.
     * @param ConditioningUnitIndexRequest $request
     * @param string $uuid
     * @return ConditioningUnitResource
     */ 
    public function show(ConditioningUnitIndexRequest $request, $uuid) {
        try {
            $item = $this->conditioningUnitRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return reponse_json_transform(['message' => 'Unité de conditionnement non trouvée'], 404);
            }
            return new ConditioningUnitResource($item);
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param ConditioningUnitStoreRequest $request
     * @return ConditioningUnitResource
     */
    public function store(ConditioningUnitStoreRequest $request)
    {
        $attributs = $request->all();

        $item = DB::transaction(function () use ($attributs) {
            // Get the authentified user id
            $user = Auth::user();
            $attributs['user_id'] = $user->id;
        
            $item = $this->conditioningUnitRepositoryEloquent->create($attributs);
            return $item;
        });

        $item = $item->fresh();

        return new ConditioningUnitResource($item);
    }
    
    /**
     * Update the specified resource in storage.
     * @param ConditioningUnitUpdateRequest $request
     * @return ConditioningUnitResource
     */
    public function update(ConditioningUnitUpdateRequest $request, $uuid)
    {
        //Check if the conditioning unit exist and return an error if not.
        try {
            $item = $this->conditioningUnitRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return reponse_json_transform(['message' => 'Unité de conditionnement non trouvée'], 404);
            }
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }

        $attributs = $request->all();

        // Get the authentified user id
        $user = Auth::user();
        $attributs['user_id'] = $user->id;

        $item = $this->conditioningUnitRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();

        return new ConditioningUnitResource($item);
    }

    /**
     * Remove the specified resource from storage.
     * @param ConditioningUnitDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(ConditioningUnitDeleteRequest $request, $uuid)
    {
        //Check if the conditioning unit exist and return an error if not.
        try {
            $conditioningUnit = $this->conditioningUnitRepositoryEloquent->findByUuid($uuid)->first();
            if (!$conditioningUnit) {
                return reponse_json_transform(['message' => 'Unité de conditionnement non trouvée'], 404);
            }
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }

        //Implement deleting conditions
        if($conditioningUnit->products->count()>0){
            $data = [
                "message" => __("Impossible de supprimer cette unité de conditionnement ! Elle est liée à au moins un produit."),
            ];

            return reponse_json_transform($data, 400);
        }
        else
        {
            $this->conditioningUnitRepositoryEloquent->delete($conditioningUnit->id);
        
            $data = [
                "message" => __("Unité de conditionnement supprimée avec succès !"),
            ];
            return reponse_json_transform($data);
        }  
    }    
}
