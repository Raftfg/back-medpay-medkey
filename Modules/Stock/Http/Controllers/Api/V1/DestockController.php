<?php

namespace Modules\Stock\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Acl\Entities\User;
use Illuminate\Support\Facades\DB;
use Modules\Stock\Entities\Destock;
use Illuminate\Support\Facades\Validator;
use App\Repositories\UserRepositoryEloquent;
use Modules\Acl\Http\Resources\UsersResource;
use Modules\Stock\Http\Resources\DestockResource;
use Modules\Stock\Http\Resources\DestocksResource;
use Modules\Stock\Http\Controllers\StockController;
use Modules\Stock\Http\Requests\DestockIndexRequest;
use Modules\Stock\Http\Requests\DestockStoreRequest;
use Modules\Stock\Http\Requests\DestockDeleteRequest;
use Modules\Stock\Http\Requests\DestockUpdateRequest;
use Modules\Stock\Repositories\DestockRepositoryEloquent;
use Modules\Stock\Repositories\TypeProductRepositoryEloquent;

class DestockController extends StockController {

     /**
     * @var DestockRepositoryEloquent
     */
    protected $destockRepositoryEloquent;

    /**
     * @var UserRepositoryEloquent
     */
    protected $userRepositoryEloquent;

    public function __construct(DestockRepositoryEloquent $destockRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent) {
        parent::__construct();
        $this->destockRepositoryEloquent = $destockRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }
    
      /**
     * Return a listing of the resource.
     * @param DestockIndexRequest $request
     * @return DestocksResource
     */
    public function index(DestockIndexRequest $request)
    {
        $donnees = $this->destockRepositoryEloquent->paginate($this->nombrePage);   
        return new DestocksResource($donnees);
    }

    /**
     * Show the specified resource.
     * @param DestockIndexRequest $request
     * @param string $uuid
     * @return DestockResource
     */ 
    public function show(DestockIndexRequest $request, $uuid) {
        // try {
        //     $item = $this->destockRepositoryEloquent->findByUuid($uuid)->first();
        //     if (!$item) {
        //         return response()->json(['message' => 'Catégorie non trouvée'], 404);
        //     }
        //     return new DestockResource($item);
        // } catch (\Exception $e) {
        //     return response()->json(['message' => 'Erreur interne du serveur'], 500);
        // }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param DestockStoreRequest $request
     * @return DestockResource
     */
    public function store(DestockStoreRequest $request)
    {
        // $attributs = $request->all();

        // //This block must be uncommented when the user model will be created and binded
        // $item = DB::transaction(function () use ($attributs) {
        // //     $user = $this->userRepositoryEloquent->findByUuid($attributs['user_id'])->first();
        // //     $attributs['user_id'] = $user->id;
        
        //     //This line must be deleted when the user model will be created and binded
        //     $attributs['user_id'] = 1;


        //     $item = $this->destockRepositoryEloquent->create($attributs);
        //     return $item;
        // });

        // $item = $item->fresh();

        // return new DestockResource($item);
    }
    
    /**
     * Update the specified resource in storage.
     * @param DestockUpdateRequest $request
     * @return DestockResource
     */
    public function update(DestockUpdateRequest $request, $uuid)
    {
        // $item = $this->destockRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        // $attributs = $request->all();

        // // $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
        // // $attributs['users_id'] = $user->id;

        // $item = $this->destockRepositoryEloquent->update($attributs, $item->id);
        // $item = $item->fresh();

        // return new DestockResource($item);
    }

    /**
     * Remove the specified resource from storage.
     * @param DestockDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(DestockDeleteRequest $request, $uuid)
    {
        // $destock = $this->destockRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?

        // //Implement deleting conditions
        // if($destock->products->count()>0){
        //     $data = [
        //         "message" => __("Impossible de supprimer cette catégorie ! Elle est liée à au moins un produit."),
        //     ];

        //     return reponse_json_transform($data, 400);
        // }
        // else
        // {
        //     $this->destockRepositoryEloquent->delete($destock->id);
        
        //     $data = [
        //         "message" => __("Catégorie supprimée avec succès !"),
        //     ];
        //     return reponse_json_transform($data);
        // }  
    }    

    public function getDestockedProductsByUserOnADate(Request $request)
    {
       
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'date' => 'required',
        ], [
            'user_id.required' => 'Le champ user est obligatoire.',
            'date.required' => 'Le champ date est obligatoire.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $date = $request->input('date');
        $userId = $request->input('user_id');

        $user = $this->userRepositoryEloquent->findByUuid($userId)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé',
            ], 404); // 404 for Not Found
        }

        $items = Destock::where('user_id', $user->id)
            ->whereDate(DB::raw('DATE(created_at)'), $date)
            ->get();

        return new DestocksResource($items);
    }



    public function getDestockersUsers()
    {
        // Retrieve all products in the specified stock
        $destockerUsers = User::join('destocks', 'users.id', '=', 'destocks.user_id')
        ->distinct('users.id') // To get distinct products based on IDs
        ->get([
            'users.*'
        ]);

        return new UsersResource($destockerUsers);
    }
}
