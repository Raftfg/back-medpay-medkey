<?php

namespace Modules\Annuaire\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Annuaire\Http\Controllers\AnnuaireController;
use Modules\Annuaire\Http\Resources\CertificationResource;
use Modules\Annuaire\Http\Resources\CertificationsResource;
use Modules\Annuaire\Http\Requests\CertificationIndexRequest;
use Modules\Annuaire\Http\Requests\CertificationStoreRequest;
use Modules\Annuaire\Http\Requests\CertificationDeleteRequest;
use Modules\Annuaire\Http\Requests\CertificationUpdateRequest;
use Modules\Annuaire\Repositories\CertificationRepositoryEloquent;

class CertificationController extends AnnuaireController {

    /**
     * @var PostRepository
     */
    protected $certificationRepositoryEloquent;

    public function __construct(CertificationRepositoryEloquent $certificationRepositoryEloquent) {
        parent::__construct();
        $this->certificationRepositoryEloquent = $certificationRepositoryEloquent;
    }
    
   /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(CertificationIndexRequest $request)
    {
        $donnees = $this->certificationRepositoryEloquent->paginate($this->nombrePage);
        return new CertificationsResource($donnees);
    }
    
   /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(CertificationStoreRequest $request)
    {
        $item = $this->certificationRepositoryEloquent->create($request->all());
        $item = $item->fresh();
        return new CertificationResource($item);
    }
    
   /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(CertificationUpdateRequest $request, $uuid)
    {
        $item = $this->certificationRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();
        $item = $this->certificationRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();
        return new CertificationResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(CertificationDeleteRequest $request, $uuid)
    {
        $certification = $this->certificationRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->certificationRepositoryEloquent->delete($certification->id);
        
        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }    
}
