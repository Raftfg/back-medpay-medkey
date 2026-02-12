<?php

namespace Modules\Administration\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Repositories\UserRepositoryEloquent;
use Modules\Administration\Http\Resources\InsuranceResource;
use Modules\Administration\Http\Resources\InsurancesResource;
use Modules\Administration\Http\Requests\InsuranceIndexRequest;
use Modules\Administration\Http\Requests\InsuranceStoreRequest;
use Modules\Administration\Http\Requests\InsuranceDeleteRequest;
use Modules\Administration\Http\Requests\InsuranceUpdateRequest;
use Modules\Administration\Http\Controllers\AdministrationController;
use Modules\Administration\Repositories\InsuranceRepositoryEloquent;


class InsuranceController extends AdministrationController
{

    /**
     * @var PostRepository
     */
    protected $insuranceRepositoryEloquent,
        $userRepositoryEloquent;

    public function __construct(InsuranceRepositoryEloquent $insuranceRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent)
    {
        parent::__construct();
        $this->insuranceRepositoryEloquent = $insuranceRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(InsuranceIndexRequest $request)
    {
        $donnees = $this->insuranceRepositoryEloquent->orderBy('created_at', 'desc')->paginate($this->nombrePage);
        return new InsurancesResource($donnees);
    }

    /**
     * Show a resource.
     *
     * @return Response
     */
    public function show(InsuranceIndexRequest $request, $uuid)
    {
        $item = $this->insuranceRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new InsuranceResource($item);
    }

    /**
     * Create a resource.
     *
     * @return Response
     */
    public function store(InsuranceStoreRequest $request)
    {

        $attributs = $request->all();
        $item = DB::transaction(function () use ($attributs) {
            // $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
            // $attributs['users_id'] = $user->id;
            $attributs['users_id'] = 1;
           
           

            $item = $this->insuranceRepositoryEloquent->create($attributs);

            return $item;
        });

        $item = $item->fresh();

        return new InsuranceResource($item);
    }

    /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(InsuranceUpdateRequest $request, $uuid)
    {

        $item = $this->insuranceRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();
        // $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
        // $attributs['users_id'] = 1;
        // \Log::info($user);



        $item = $this->insuranceRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();
        return new InsuranceResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(InsuranceDeleteRequest $request, $uuid)
    {
        $insurance = $this->insuranceRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->insuranceRepositoryEloquent->delete($insurance->id);

        $data = [
            "message" => __("Assurance supprimée avec succès"),
        ];
        return reponse_json_transform($data);
    }

    public function getInsuranceWithPacks($uuid)
    {
        $insurance = $this->insuranceRepositoryEloquent->findByUuidOrFail($uuid)->first();
        // $insurance = Insurance::getInsuranceWithPacks($insuranceId);
    
        if (!$insurance) {
            return response()->json([
                'success' => false,
                'message' => 'L\'assurance n\'existe pas.',
            ], 404);
        }
    
        $data = [
            'insurance' => $insurance,
            'packs' => $insurance->packs,
        ];
    
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
