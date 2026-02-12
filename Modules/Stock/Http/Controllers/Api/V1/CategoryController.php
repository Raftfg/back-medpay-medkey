<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepositoryEloquent;
use Modules\Stock\Http\Resources\CategoryResource;
use Modules\Stock\Http\Resources\CategoriesResource;
use Modules\Stock\Http\Requests\CategoryIndexRequest;
use Modules\Stock\Http\Requests\CategoryStoreRequest;
use Modules\Stock\Http\Requests\CategoryDeleteRequest;
use Modules\Stock\Http\Requests\CategoryUpdateRequest;
use Modules\Stock\Http\Controllers\StockController;
use Modules\Stock\Repositories\CategoryRepositoryEloquent;
use Modules\Stock\Repositories\TypeProductRepositoryEloquent;

class CategoryController extends StockController {

     /**
     * @var CategoryRepositoryEloquent
     */
    protected $categoryRepositoryEloquent;

    /**
     * @var UserRepositoryEloquent
     */
    protected $userRepositoryEloquent;

    /**
     * @var TypeProductRepositoryEloquent
     */
    protected $typeProductRepositoryEloquent;

    public function __construct(CategoryRepositoryEloquent $categoryRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent, TypeProductRepositoryEloquent $typeProductRepositoryEloquent) {
        parent::__construct();
        $this->categoryRepositoryEloquent = $categoryRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
        $this->typeProductRepositoryEloquent = $typeProductRepositoryEloquent;
    }
    
      /**
     * Return a listing of the resource.
     * @param CategoryIndexRequest $request
     * @return CategoriesResource
     */
    public function index(CategoryIndexRequest $request)
    {
        $donnees = $this->categoryRepositoryEloquent->paginate($this->nombrePage);   
        return new CategoriesResource($donnees);
    }

    /**
     * Show the specified resource.
     * @param CategoryIndexRequest $request
     * @param string $uuid
     * @return CategoryResource
     */ 
    public function show(CategoryIndexRequest $request, $uuid) {
        try {
            $item = $this->categoryRepositoryEloquent->findByUuid($uuid)->first();
            if (!$item) {
                return response()->json(['message' => 'Catégorie non trouvée'], 404);
            }
            return new CategoryResource($item);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param CategoryStoreRequest $request
     * @return CategoryResource
     */
    public function store(CategoryStoreRequest $request)
    {
        $attributs = $request->all();

        //This block must be uncommented when the user model will be created and binded
        $item = DB::transaction(function () use ($attributs) {
        //     $user = $this->userRepositoryEloquent->findByUuid($attributs['user_id'])->first();
        //     $attributs['user_id'] = $user->id;
        
            //This line must be deleted when the user model will be created and binded
            $attributs['user_id'] = 1;


            $item = $this->categoryRepositoryEloquent->create($attributs);
            return $item;
        });

        $item = $item->fresh();

        return new CategoryResource($item);
    }
    
    /**
     * Update the specified resource in storage.
     * @param CategoryUpdateRequest $request
     * @return CategoryResource
     */
    public function update(CategoryUpdateRequest $request, $uuid)
    {
        $item = $this->categoryRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        // $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
        // $attributs['users_id'] = $user->id;

        $item = $this->categoryRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();

        return new CategoryResource($item);
    }

    /**
     * Remove the specified resource from storage.
     * @param CategoryDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(CategoryDeleteRequest $request, $uuid)
    {
        $category = $this->categoryRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?

        //Implement deleting conditions
        if($category->products->count()>0){
            $data = [
                "message" => __("Impossible de supprimer cette catégorie ! Elle est liée à au moins un produit."),
            ];

            return reponse_json_transform($data, 400);
        }
        else
        {
            $this->categoryRepositoryEloquent->delete($category->id);
        
            $data = [
                "message" => __("Catégorie supprimée avec succès !"),
            ];
            return reponse_json_transform($data);
        }  
    }    
}
