<?php

namespace Modules\Administration\Http\Controllers\Api\V1;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Administration\Entities\Pack;
use App\Repositories\UserRepositoryEloquent;
use Modules\Administration\Entities\Insurance;
use Modules\Administration\Http\Resources\PackResource;
use Modules\Administration\Http\Resources\PacksResource;
use Modules\Administration\Http\Requests\PackIndexRequest;
use Modules\Administration\Http\Requests\PackStoreRequest;
use Modules\Administration\Http\Requests\PackDeleteRequest;
use Modules\Administration\Http\Requests\PackUpdateRequest;
use Modules\Administration\Repositories\PackRepositoryEloquent;
use Modules\Administration\Repositories\InsuranceRepositoryEloquent;
use Modules\Administration\Http\Controllers\AdministrationController;
use Modules\Administration\Repositories\ProductTypeRepositoryEloquent;

class PackController extends AdministrationController
{

    /**
     * @var PostRepository
     */
    protected $packRepositoryEloquent,
        $userRepositoryEloquent, $insuranceRepositoryEloquent,
        $producttypeRepositoryEloquent;

    public function __construct(PackRepositoryEloquent $packRepositoryEloquent, UserRepositoryEloquent $userRepositoryEloquent, InsuranceRepositoryEloquent $insuranceRepositoryEloquent, ProductTypeRepositoryEloquent $producttypeRepositoryEloquent)
    {
        parent::__construct();
        $this->packRepositoryEloquent = $packRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
        $this->insuranceRepositoryEloquent = $insuranceRepositoryEloquent;
        $this->producttypeRepositoryEloquent = $producttypeRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(PackIndexRequest $request)
    {
        $donnees = $this->packRepositoryEloquent->orderBy('created_at', 'desc')->paginate($this->nombrePage);
        return new PacksResource($donnees);
    }

    /**
     * Show a resource.
     *
     * @return Response
     */
    public function show(PackIndexRequest $request, $uuid)
    {
        $item = $this->packRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        return new PackResource($item);
    }

    /**
     * Create a resource.
     *
     * @return Response
     */

public function store(PackStoreRequest $request)
{
    // Récupérez toutes les données du formulaire
    $attributes = $request->all();

    // Récupérez l'assurance associée
    $insurance = $this->insuranceRepositoryEloquent->findByUuid($attributes['insurances_id'])->first();
   
    // Vérifiez si l'assurance existe
    if (!$insurance) {
        return response()->json([
            'success' => false,
            'message' => 'L\'assurance sélectionnée est invalide.',
        ], 400);
    }

    // Mettez à jour l'ID de l'assurance avec l'ID récupéré
    $attributes['insurances_id'] = $insurance->id;

      // Récupérez l'utilisateur connecté
    //   $user = $this->userRepositoryEloquent->findByUuid($attributes['users_id'])->first();
      
    //   // Vérifiez si l'utilisateur existe
    //   if (!$user) {
    //       return response()->json([
    //           'success' => false,
    //           'message' => 'L\'utilisateur connecté est invalide.',
    //         ], 400);
    //     }
        // \Log::info($user);

      // Mettez à jour l'ID de l'utilisateur avec l'ID récupéré
      $attributes['users_id'] = 1;

    // Vérifiez si les données des packs sont présentes
    if (!isset($attributes['packs']) || empty($attributes['packs'])) {
        return response()->json([
            'success' => false,
            'message' => 'Les données des packs sont requises et ne peuvent pas être vides.',
        ], 400);
    }

    $packsData = $attributes['packs'];
    $packs = [];

    // Enregistrez chaque pack
    foreach ($packsData as $packData) {
        $pack = [
            'designation' => $packData['designation'],
            'percentage' => $packData['percentage'],
            'insurances_id' => $attributes['insurances_id'],
            // 'users_id' => $userId,
            'users_id' => $attributes['users_id'],
        ];

        $createdPack = $this->packRepositoryEloquent->create($pack);

        $packs[] = $createdPack->fresh(); // Ajoutez le pack frais au tableau
    }

    return new  PacksResource($packs);
}

    /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(PackUpdateRequest $request, $uuid)
    {
        $item = $this->packRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->all();

        // $user = $this->userRepositoryEloquent->findByUuid($attributs['users_id'])->first();
        $attributs['users_id'] = 1;

        if (isset($attributs['insurances_id'])) {
            $insurance = $this->insuranceRepositoryEloquent->findByUuid($attributs['insurances_id'])->first();
            $attributs['insurances_id'] = $insurance->id;
        }

        // if (isset($attributs['product_types_id'])) {
        //     $producttype = $this->producttypeRepositoryEloquent->findByUuid($attributs['product_types_id'])->first();
        //     $attributs['product_types_id'] = $producttype->id;
        // }

        $item = $this->packRepositoryEloquent->update($attributs, $item->id);
        $item = $item->fresh();
        return new PackResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(PackDeleteRequest $request, $uuid)
    {
        $pack = $this->packRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->packRepositoryEloquent->delete($pack->id);

        $data = [
            "message" => __("Item supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }

    public function getPackByInsurance($uuid)
    {

        // $insuranceId = $request->input('insuranceId');
        $item = $this->insuranceRepositoryEloquent->findByUuidOrFail($uuid)->first();

        // Remplacez "Insurance" par le nom de votre modèle d'assurance
        $packs = $this->packRepositoryEloquent->where('insurances_id', $item->id)->get();

        return response()->json($packs);
    }

    public function getPacksOfInsuranceByPack($uuid)
    {

        // $insuranceId = $request->input('insuranceId');
        // $item = $this->insuranceRepositoryEloquent->findByUuidOrFail($uuid)->first();

        //get pack
        $pack = $this->packRepositoryEloquent->findByUuidOrFail($uuid)->first();
        $insuranceId = $pack->insurances_id;

        // get all packs related to the $insuranceId
        $packs = $this->packRepositoryEloquent->where('insurances_id', $insuranceId)->get();

        return new  PacksResource($packs);
    }

}