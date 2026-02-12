<?php

namespace Modules\Annuaire\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Annuaire\Http\Controllers\AnnuaireController;
use Modules\Annuaire\Http\Resources\FormationResource;
use Modules\Annuaire\Http\Resources\FormationsResource;
use Modules\Annuaire\Http\Requests\FormationIndexRequest;
use Modules\Annuaire\Http\Requests\FormationStoreRequest;
use Modules\Annuaire\Http\Requests\FormationDeleteRequest;
use Modules\Annuaire\Http\Requests\FormationUpdateRequest;
use Modules\Annuaire\Repositories\FormationRepositoryEloquent;

class FormationController extends AnnuaireController {

    /**
     * @var PostRepository
     */
    protected $formationRepositoryEloquent;

    public function __construct(FormationRepositoryEloquent $formationRepositoryEloquent) {
        parent::__construct();
        $this->formationRepositoryEloquent = $formationRepositoryEloquent;
    }
    
   /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(FormationIndexRequest $request)
    {
        $donnees = $this->formationRepositoryEloquent->paginate($this->nombrePage);
        return new FormationsResource($donnees);
    }
    
   /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(FormationStoreRequest $request)
    {
        $item = $this->formationRepositoryEloquent->create($request->all());
        $item = $item->fresh();
        return new FormationResource($item);
    }
    
   /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(FormationUpdateRequest $request, $uuid)
    {
        $item = $this->formationRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();
        $item = $this->formationRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();
        return new FormationResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(FormationDeleteRequest $request, $uuid)
    {
        $formation = $this->formationRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->formationRepositoryEloquent->delete($formation->id);
        
        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }    
}
