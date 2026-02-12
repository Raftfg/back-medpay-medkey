<?php

namespace Modules\Payment\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

// use App\Repositories\UserRepositoryEloquent;
use Modules\User\Repositories\UserRepositoryEloquent;
use Modules\Payment\Repositories\OperationRepositoryEloquent;
use Modules\Movment\Repositories\MovmentRepositoryEloquent;
use Modules\Cash\Repositories\CashRegisterRepositoryEloquent;

use Modules\Payment\Http\Resources\OperationResource;
use Modules\Payment\Http\Resources\OperationsResource;

use Modules\Payment\Http\Controllers\PaymentController;

use Modules\Payment\Http\Requests\OperationIndexRequest;
use Modules\Payment\Http\Requests\OperationStoreRequest;
use Modules\Payment\Http\Requests\OperationDeleteRequest;
use Modules\Payment\Http\Requests\OperationUpdateRequest;


class OperationController extends PaymentController {

    /**
     * @var PostRepository
     */
    protected $operationRepositoryEloquent, $userRepositoryEloquent, $movmentRepositoryEloquent, $cashRegisterRepositoryEloquent;

    public function __construct(OperationRepositoryEloquent $operationRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent, MovmentRepositoryEloquent $movmentRepositoryEloquent, CashRegisterRepositoryEloquent $cashRegisterRepositoryEloquent) {
        parent::__construct();
        $this->operationRepositoryEloquent = $operationRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
        $this->movmentRepositoryEloquent = $movmentRepositoryEloquent;
        $this->cashRegisterRepositoryEloquent = $cashRegisterRepositoryEloquent;
    }
    
   /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(OperationIndexRequest $request)
    {
        $donnees = $this->operationRepositoryEloquent->paginate($this->nombrePage);
        return new OperationsResource($donnees);
    }
    
   /**
     * Create a resource.
     *
     * @return Response
     */
    public function show(OperationIndexRequest $request, $uuid) {
        $item = $this->operationRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new OperationResource($item);
    }

    public function store(OperationStoreRequest $request)
    {
        $attributs = $request->all();

        // dd($attributs['users_id']);

        $item = DB::transaction(function () use ($attributs) {
            $user = $this->userRepositoryEloquent->findByUuid($attributs['user_id'])->first();
            // $attributs['users_id'] = $user->id;
            $attributs['user_id'] = NULL;
            $movment = $this->movmentRepositoryEloquent->findByUuid($attributs['movement_id'])->first();
            // $attributs['movement_id'] = $movment->id;
            $attributs['movement_id'] =  4;
            
            
            $cashRegister = $this->cashRegisterRepositoryEloquent->findByUuid($attributs['cash_register_id'])->first();
            // $attributs['cash_register_id'] = $cashRegister->id;
            $attributs['cash_register_id'] = 2;

            //department à gérer 

            $item = $this->operationRepositoryEloquent->create($attributs);

            return $item; // dLA transaction se termine avec return Item
        });

        $item = $item->fresh(); // recupere la ressource nouvellement créee
        return new OperationResource($item); // retourne la ressource sous forme de JSON
    }
    
   /**
     * Update a resource.
     *
     * @return Response
     */
    // public function update(OperationUpdateRequest $request, $uuid)
    // {
    //     $item = $this->operationRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
    //     $attributs = $request->all();

    //     $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
    //     $attributs['users_id'] = $user->id;
        
    //     $typeOperation = $this->typeOperationRepositoryEloquent->findByUuid($attributs['type_Operations_id'])->first();
    //     $attributs['type_Operations_id'] = $typeOperation->id;

    //     //department à gérer 

    //     $item = $this->operationRepositoryEloquent->update($attributs, $item->id);
    //     $item = $item->fresh();
    //     return new OperationResource($item);
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(OperationDeleteRequest $request, $uuid)
    {
        $Operation = $this->operationRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->operationRepositoryEloquent->delete($Operation->id);
        
        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }    
}
