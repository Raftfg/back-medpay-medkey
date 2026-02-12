<?php

namespace Modules\Annuaire\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Annuaire\Http\Controllers\AnnuaireController;
use Modules\Annuaire\Http\Resources\CompetenceResource;
use Modules\Annuaire\Http\Resources\CompetencesResource;
use Modules\Annuaire\Http\Requests\CompetenceIndexRequest;
use Modules\Annuaire\Http\Requests\CompetenceStoreRequest;
use Modules\Annuaire\Http\Requests\CompetenceDeleteRequest;
use Modules\Annuaire\Http\Requests\CompetenceUpdateRequest;
use Modules\Annuaire\Repositories\CompetenceRepositoryEloquent;

class CompetenceController extends AnnuaireController {

    /**
     * @var PostRepository
     */
    protected $competenceRepositoryEloquent;

    public function __construct(CompetenceRepositoryEloquent $competenceRepositoryEloquent) {
        parent::__construct();
        $this->competenceRepositoryEloquent = $competenceRepositoryEloquent;
    }
    
   /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(CompetenceIndexRequest $request)
    {
        $donnees = $this->competenceRepositoryEloquent->paginate($this->nombrePage);
        return new CompetencesResource($donnees);
    }
    
   /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(CompetenceStoreRequest $request)
    {
        $item = $this->competenceRepositoryEloquent->create($request->all());
        $item = $item->fresh();
        return new CompetenceResource($item);
    }
    
   /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(CompetenceUpdateRequest $request, $uuid)
    {
        $item = $this->competenceRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();
        $item = $this->competenceRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();
        return new CompetenceResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(CompetenceDeleteRequest $request, $uuid)
    {
        $competence = $this->competenceRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->competenceRepositoryEloquent->delete($competence->id);
        
        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }    
}
