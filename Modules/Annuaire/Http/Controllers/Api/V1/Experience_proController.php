<?php

namespace Modules\Annuaire\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Annuaire\Http\Controllers\AnnuaireController;
use Modules\Annuaire\Http\Resources\Experience_proResource;
use Modules\Annuaire\Http\Resources\Experience_prosResource;
use Modules\Annuaire\Http\Requests\Experience_proIndexRequest;
use Modules\Annuaire\Http\Requests\Experience_proStoreRequest;
use Modules\Annuaire\Http\Requests\Experience_proDeleteRequest;
use Modules\Annuaire\Http\Requests\Experience_proUpdateRequest;
use Modules\Annuaire\Repositories\Experience_proRepositoryEloquent;

class Experience_proController extends AnnuaireController {

    /**
     * @var PostRepository
     */
    protected $experience_proRepositoryEloquent;

    public function __construct(Experience_proRepositoryEloquent $experience_proRepositoryEloquent) {
        parent::__construct();
        $this->experience_proRepositoryEloquent = $experience_proRepositoryEloquent;
    }
    
   /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Experience_proIndexRequest $request)
    {
        $donnees = $this->experience_proRepositoryEloquent->paginate($this->nombrePage);
        return new Experience_prosResource($donnees);
    }
    
   /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(Experience_proStoreRequest $request)
    {
        $item = $this->experience_proRepositoryEloquent->create($request->all());
        $item = $item->fresh();
        return new Experience_proResource($item);
    }
    
   /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(Experience_proUpdateRequest $request, $uuid)
    {
        $item = $this->experience_proRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();
        $item = $this->experience_proRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();
        return new Experience_proResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(Experience_proDeleteRequest $request, $uuid)
    {
        $experience_pro = $this->experience_proRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->experience_proRepositoryEloquent->delete($experience_pro->id);
        
        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }    
}
