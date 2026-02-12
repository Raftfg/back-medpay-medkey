<?php

namespace Modules\Hospitalization\Http\Controllers\Api\V1;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Hospitalization\Entities\Bed;
use Modules\Acl\Repositories\UserRepository;

use Modules\Hospitalization\Http\Resources\BedsResource;
use Modules\Hospitalization\Http\Resources\RoomResource;
use Modules\Hospitalization\Http\Resources\RoomsResource;
use Modules\Hospitalization\Http\Requests\RoomIndexRequest;
use Modules\Hospitalization\Http\Requests\RoomStoreRequest;
use Modules\Hospitalization\Http\Requests\RoomDeleteRequest;
use Modules\Hospitalization\Http\Requests\RoomUpdateRequest;
use Modules\Hospitalization\Repositories\RoomRepositoryEloquent;
use Modules\Hospitalization\Http\Controllers\HospitalizationController;

class RoomController extends HospitalizationController {

    /**
     * @var RoomRepositoryEloquent
     */
    protected $roomRepositoryEloquent;
    
    /**
     * @var UserRepository
     */
    protected $userRepositoryEloquent;

    public function __construct(
        RoomRepositoryEloquent $roomRepositoryEloquent,
        UserRepository $userRepositoryEloquent,
        ) {
        parent::__construct();
        $this->roomRepositoryEloquent = $roomRepositoryEloquent;
        $this->userRepositoryEloquent = $userRepositoryEloquent;
    }

    
    /**
     * Return a listing of the resource.
     * @param RoomIndexRequest $request
     * @return RoomsResource
     */
    public function index(RoomIndexRequest $request)
    {
        try {
            // Charger la relation beds et service pour éviter les erreurs null
            $data = $this->roomRepositoryEloquent
                ->with(['beds', 'service'])
                ->paginate($this->nombrePage);
            return new RoomsResource($data);
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la récupération des salles: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return reponse_json_transform(['message' => 'Erreur lors de la récupération des salles: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the specified resource.
     * @param RoomIndexRequest $request
     * @param string $uuid
     * @return RoomResource
     */ 
    public function show(RoomIndexRequest $request, $uuid) 
    {
        try {
            $item = $this->roomRepositoryEloquent->findByUuid($uuid)->first();

            if (!$item) {
                return reponse_json_transform(['message' => 'Salle non trouvée'], 404);
            }

            return new RoomResource($item);
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Store a newly created resource in storage.
     * @param RoomStoreRequest $request
     * @return RoomResource
     */
    public function store(RoomStoreRequest $request)
    {
        try {
            $attributes = $request->all();

            $item = DB::transaction(function () use ($attributes) {
                // Need review to store the Auth::user()
                $attributes['user_id'] = 1;

                //Generate the room code based on:
                //The prefix SALLE
                //Then followed by five random number
                $prefix = "SALLE";

                // Define a variable to store the generated code
                $generatedCode = '';

                do {
                    // Generate a random 5-digit number and pad it with leading zeros
                    $randomNumbers = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

                    // Construct the code
                    $generatedCode = $prefix . '-' . $randomNumbers;

                    // Check if a product with this code already exists
                    $existingCode = $this->roomRepositoryEloquent->findByCode($generatedCode);
                } while ($existingCode);

                // Add the generated code to the attributs
                $attributes['code'] = $generatedCode;

                $item = $this->roomRepositoryEloquent->create($attributes);
                return $item;
            });

            $item = $item->fresh();

            return new RoomResource($item);
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }
    }
    
    /**
     * Update the specified resource in storage.
     * @param RoomUpdateRequest $request
     * @return RoomResource
     */
    public function update(RoomUpdateRequest $request, $uuid)
    {
        try {
            $item = $this->roomRepositoryEloquent->findByUuid($uuid)->first();

            if (!$item) {
                return reponse_json_transform(['message' => 'Salle non trouvée'], 404);
            }

            $attributes = $request->all();
            // Need review to store the Auth::user()
            $attributes['user_id'] = 1;
    
            $item = $this->roomRepositoryEloquent->update($attributes, $item->id);
            $item = $item->fresh();

            return new RoomResource($item);
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param RoomDeleteRequest $request
     * @param string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy(RoomDeleteRequest $request, $uuid)
    {
        try {
            $item = $this->roomRepositoryEloquent->findByUuid($uuid)->first();

            if (!$item) {
                return reponse_json_transform(['message' => 'Salle non trouvée'], 404);
            }

            if ($item->beds->count() > 0) {
                $data = ["message" => __("Impossible de supprimer cette salle ! Elle est lié à au moins un lit.")];
                return reponse_json_transform($data, 400);
            } else {
                $this->roomRepositoryEloquent->delete($item->id);
                $data = ["message" => __("Salle supprimé avec succès !")];
                return reponse_json_transform($data);
            } 
        } catch (\Exception $e) {
            return reponse_json_transform(['message' => 'Erreur interne du serveur'], 500);
        }
    }    

    public function getFreeBeds($uuid)
    {
        $room = $this->roomRepositoryEloquent->findByUuid($uuid)->first();
    
        $freeBeds = Bed::where('room_id', $room->id)
            ->whereDoesntHave('allPatients', function ($query) {
                $query->whereDate('end_occupation_date', '>=', Carbon::today());
            })
            ->get();
    
        return new BedsResource($freeBeds);
    }
}
