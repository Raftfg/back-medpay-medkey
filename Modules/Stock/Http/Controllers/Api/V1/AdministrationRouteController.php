<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Repositories\UserRepositoryEloquent;
use Symfony\Component\HttpFoundation\Request;
use Modules\Stock\Entities\AdministrationRoute;
use Modules\Stock\Http\Controllers\StockController;
use Modules\Stock\Http\Resources\AdministrationRouteResource;
use Modules\Stock\Http\Resources\AdministrationRoutesResource;
use Modules\Stock\Http\Requests\AdministrationRouteIndexRequest;
use Modules\Stock\Http\Requests\AdministrationRouteStoreRequest;
use Modules\Stock\Http\Requests\AdministrationRouteDeleteRequest;
use Modules\Stock\Http\Requests\AdministrationRouteUpdateRequest;
use Modules\Stock\Repositories\AdministrationRouteRepositoryEloquent;

class AdministrationRouteController extends StockController {

     /**
     * @var AdministrationRouteRepositoryEloquent
     */
    protected $administrationRouteRepositoryEloquent;

    /**
     * @var UserRepositoryEloquent
     */
    protected $userRepositoryEloquent;

    public function __construct(AdministrationRouteRepositoryEloquent $administrationRouteRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent) {
        parent::__construct();
        $this->administrationRouteRepositoryEloquent = $administrationRouteRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }
    
    /**
     * Return a listing of the resource.
     * @param AdministrationRouteIndexRequest $request
     * @return AdministrationRoutesResource
     */
    public function index(AdministrationRouteIndexRequest $request)
    {
        $donnees = $this->administrationRouteRepositoryEloquent->paginate($this->nombrePage);   
        return new AdministrationRoutesResource($donnees);
    }

    /**
     * Show the specified resource.
     * @param AdministrationRouteIndexRequest $request
     * @param string $uuid
     * @return AdministrationRouteResource
     */ 
    public function show(AdministrationRouteIndexRequest $request, $uuid) {
        try {
            $item = $this->administrationRouteRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return reponse_json_transform(['message' => 'Voie d\'administration non trouvée'], 404);
            }
            return new AdministrationRouteResource($item);
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param AdministrationRouteStoreRequest $request
     * @return AdministrationRouteResource
     */
    public function store(AdministrationRouteStoreRequest $request)
    {
        $attributs = $request->all();

        $item = DB::transaction(function () use ($attributs) {
            // Get the authentified user id
            $user = Auth::user();
            $attributs['user_id'] = $user->id;
        
            $item = $this->administrationRouteRepositoryEloquent->create($attributs);
            return $item;
        });

        $item = $item->fresh();

        return new AdministrationRouteResource($item);
    }
    
    /**
     * Update the specified resource in storage.
     * @param AdministrationRouteUpdateRequest $request
     * @return AdministrationRouteResource
     */
    public function update(AdministrationRouteUpdateRequest $request, $uuid)
    {
        //Check if the administration route exist and return an error if not.
        try {
            $item = $this->administrationRouteRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return reponse_json_transform(['message' => 'Voie d\'administration non trouvée'], 404);
            }
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }

        $attributs = $request->all();

        // Get the authentified user id
        $user = Auth::user();
        $attributs['user_id'] = $user->id;

        $item = $this->administrationRouteRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();

        return new AdministrationRouteResource($item);
    }

    /**
     * Remove the specified resource from storage.
     * @param AdministrationRouteDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(AdministrationRouteDeleteRequest $request, $uuid)
    {
        //Check if the administration route exist and return an error if not.
        try {
            $administrationRoute = $this->administrationRouteRepositoryEloquent->findByUuid($uuid)->first();
            if (!$administrationRoute) {
                return reponse_json_transform(['message' => 'Voie d\'administration non trouvée'], 404);
            }
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }

        //Implement deleting conditions
        if($administrationRoute->products->count()>0){
            $data = [
                "message" => __("Impossible de supprimer cette voie d'administration ! Elle est liée à au moins un produit."),
            ];

            return reponse_json_transform($data, 400);
        }
        else
        {
            $this->administrationRouteRepositoryEloquent->delete($administrationRoute->id);
        
            $data = [
                "message" => __("Voie d'administration supprimée avec succès !"),
            ];
            return reponse_json_transform($data);
        }  
    }    
}
