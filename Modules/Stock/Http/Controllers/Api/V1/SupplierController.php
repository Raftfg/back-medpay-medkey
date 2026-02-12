<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Http\Resources\SupplierResource;
use Modules\Stock\Http\Resources\SuppliersResource;
use Modules\Stock\Http\Requests\SupplierIndexRequest;
use Modules\Stock\Http\Requests\SupplierStoreRequest;
use Modules\Stock\Http\Requests\SupplierDeleteRequest;
use Modules\Stock\Http\Requests\SupplierUpdateRequest;
use Modules\Stock\Http\Controllers\StockController;
use App\Repositories\UserRepositoryEloquent;
use Modules\Stock\Repositories\SupplierRepositoryEloquent;

class SupplierController extends StockController {

    /**
     * @var SupplierRepositoryEloquent
     */
    protected $supplierRepositoryEloquent;

    /**
     * @var UserRepositoryEloquent
     */
    protected $userRepositoryEloquent;

    public function __construct(SupplierRepositoryEloquent $supplierRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent) {
        parent::__construct();
        $this->supplierRepositoryEloquent = $supplierRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }
    
    /**
     * Return a listing of the resource.
     * @param SupplierIndexRequest $request
     * @return SuppliersResource
     */
    public function index(SupplierIndexRequest $request)
    {
        $donnees = $this->supplierRepositoryEloquent->paginate($this->nombrePage);
        return new SuppliersResource($donnees);
    }

    /**
     * Show the specified resource.
     * @param SupplierIndexRequest $request
     * @param string $uuid
     * @return SupplierResource
     */ 
    public function show(SupplierIndexRequest $request, $uuid) {
        try {
            $item = $this->supplierRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return reponse_json_transform(['message' => 'Fournisseur non trouvé'], 404);
            }
            return new SupplierResource($item);
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param SupplierStoreRequest $request
     * @return SupplierResource
     */
    public function store(SupplierStoreRequest $request)
    {
        $attributs = $request->all();

        $item = DB::transaction(function () use ($attributs) {
            // Need review to store the Auth::user()
            $attributs['user_id'] = 1;

            $item = $this->supplierRepositoryEloquent->create($attributs);
            return $item;
        });

        $item = $item->fresh();

        return new SupplierResource($item);
    }
    
    /**
     * Update the specified resource in storage.
     * @param SupplierUpdateRequest $request
     * @return SupplierResource
     */
    public function update(SupplierUpdateRequest $request, $uuid)
    {
        try {
            $item = $this->supplierRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return reponse_json_transform(['message' => 'Fournisseur non trouvé'], 404);
            }
            $attributs = $request->all();
            // Need review to store the Auth::user()
            $attributs['user_id'] = 1;
    
            $item = $this->supplierRepositoryEloquent->update($attributs, $item->id);
            $item = $item->fresh();
            return new SupplierResource($item);
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param SupplierDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(SupplierDeleteRequest $request, $uuid)
    {
        try {
            $item = $this->supplierRepositoryEloquent->findByUuid($uuid)->first();

            if (!$item) {
                return reponse_json_transform(['message' => 'Fournisseur non trouvé'], 404);
            }

            if ($item->supplyProducts->count() > 0) {
                $data = ["message" => __("Impossible de supprimer ce fournisseur ! Il est lié à au moins un approvisionnement.")];
    
                return reponse_json_transform($data, 400);
            } else {
                $this->supplierRepositoryEloquent->delete($item->id);
    
                $data = ["message" => __("Fournisseur supprimé avec succès !")];
                
                return reponse_json_transform($data);
            } 
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }
    }    
}
