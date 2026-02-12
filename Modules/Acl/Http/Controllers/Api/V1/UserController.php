<?php

namespace Modules\Acl\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Modules\Acl\Entities\Role;
use Modules\Acl\Entities\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Modules\Acl\Http\Resources\UserResource;
use Modules\Acl\Repositories\RoleRepository;
use Modules\Acl\Repositories\UserRepository;
use Modules\Acl\Http\Resources\UsersResource;
use App\Http\Controllers\Api\V1\ApiController;
use Modules\Acl\Http\Requests\UserIndexRequest;
use Modules\Acl\Http\Requests\UserStoreRequest;
use Modules\Acl\Http\Requests\UserDeleteRequest;
use Modules\Acl\Http\Requests\UserUpdateRequest;
use Modules\Acl\Http\Requests\UserTeleverserRequest;
use Modules\Acl\Http\Requests\UserEnvoiNotificationRequest;

class UserController extends \Modules\Acl\Http\Controllers\AclController
{
    use \Modules\Media\Traits\TeleverseTrait;
    use \Modules\Acl\Traits\EnvoiNotificationUserTrait;

    /**
     * @var PostRepository
     */
    protected $userRepositoryEloquent, $roleRepositoryEloquent;

    public function __construct(UserRepository $userRepositoryEloquent, RoleRepository $roleRepositoryEloquent)
    {
        parent::__construct();
        $this->userRepositoryEloquent = $userRepositoryEloquent;
        $this->roleRepositoryEloquent = $roleRepositoryEloquent;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(UserIndexRequest $request)
    {

        // $user = Auth::user();
//          $current_user = user_api()->id;
// \Log::info( $current_user);
// dd( $current_user);
        $queryBuilder = filtre_recherche_builder(
            $request->recherche,
            $this->userRepositoryEloquent->getModel(),
            $this->userRepositoryEloquent->query()
        );
       
        $donnees = $queryBuilder->orderBy('created_at', 'DESC')->paginate($this->nombrePage);
        return new UsersResource($donnees);
    }

    /**
     * Create a resource.
     *
     * @return Response
     */
    
    public function store(UserStoreRequest $request)
    {
        $attributs = $request->except(['tenant_id']);   //sanitinize
        $attributs['password'] = Hash::make($request->password);
    
        $user = [
            'name' => $attributs['name'],
            'prenom' => $attributs['prenom'],
            'email' => $attributs['email'],
            'sexe' => $attributs['sexe'],
            'telephone' => $attributs['telephone'],
            'adresse' => $attributs['adresse'],
            'password' => $attributs['password'],
           
        ];
        $item = DB::transaction(function () use ($attributs, $user) {
            $role = $this->roleRepositoryEloquent->findByUuid($attributs['role_id'])->first();
            $item = $this->userRepositoryEloquent->create($user);
            $item->assignRole($role->name);
            return $item;
        });
        $item = $item->fresh();
        return new UserResource($item);
    }

    /**
     * Show a resource.
     *
     * @return Response
     */
    public function show(UserIndexRequest $request, $uuid)
    {
        try {
            // Utiliser findByUuid au lieu de findByUuidOrFail pour éviter abort(404)
            $items = $this->userRepositoryEloquent->findByUuid($uuid);
            $item = $items->first();
            
            if (!$item) {
                return reponse_json_transform([
                    'message' => 'Utilisateur non trouvé.',
                    'error' => 'L\'utilisateur avec cet UUID n\'existe pas dans ce tenant.'
                ], 404);
            }
            
            return new UserResource($item);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de l\'utilisateur: ' . $e->getMessage());
            return reponse_json_transform([
                'message' => 'Erreur lors de la récupération de l\'utilisateur.',
                'error' => config('app.debug') ? $e->getMessage() : 'Une erreur est survenue'
            ], 500);
        }
    }

    /**
     * Téléverser le document
     * 
     * @param type $request
     * @param string $uuid
     */
    public function televerser(UserTeleverserRequest $request, $uuid)
    {
        //\Log::info($uuid);
        $item = $this->userRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element ?
        $documents = $request->file('documents');
        //\Log::info($documents);
        $this->saveMediasApiTenant($item, $documents, $this->mediaCollectionName, $this->mediaDisk, null);

        $moduleAlias = strtolower(config('acl.name'));
        $media_collection_name = config("$moduleAlias.media_collection_name");

        return reponse_json_transform([
            'message' => "Succes",
            'medias' => $item->obtenirMediaUrlsFormates($media_collection_name),
        ]);
    }

    /**
     * Update a resource.
     *
     * @return Response
     */
    public function update(UserUpdateRequest $request, $uuid)


    {
       
        $user = $this->userRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $attributs = $request->except(['tenant_id']);   //sanitinize
        $userData = [
            'name' => $attributs['name'],
            'prenom' => $attributs['prenom'],
            'email' => $attributs['email'],
            'sexe' => $attributs['sexe'],
            'telephone' => $attributs['telephone'],
            'adresse' => $attributs['adresse'],
        ];
        $item = DB::transaction(function () use ($attributs, $user, $userData) {
            $role = $this->roleRepositoryEloquent->findByUuid($attributs['role_id'])->first();
            // $item = $this->userRepositoryEloquent->create($user);
            $item = $this->userRepositoryEloquent->modifier($userData, $user);
            $item->syncRoles([$role->name]);
            return $item;
        });
        $item = $item->fresh();
        return new UserResource($item);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
   
    public function destroy(UserDeleteRequest $request, $uuid)
    {
        $user = $this->userRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        //@TODO : Implémenter les conditions de suppression
        $this->userRepositoryEloquent->delete($user->id);

        $data = [
            "message" => __("Utilisateur supprimé avec succès"),
        ];
        return reponse_json_transform($data);
    }

    /**
     * Envoyer le mot de passe temporaire
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function genererMotPasseTemporaire(UserEnvoiNotificationRequest $request, $uuid)
    {
        $user = $this->userRepositoryEloquent->findByUuidOrFail($uuid)->first();  //existe-il cet element?
        $motDePasse = $this->userRepositoryEloquent->genererMotDePasseAleatoire($user);
        $motDePasse = $motDePasse ?: "";
        $this->envoiMotPasseTemporaire($user, $motDePasse);

        $data = [
            "message" => __("Envoyé avec succès"),
        ];
        return reponse_json_transform($data);
    }

    public function getUserRoleUUID($userUUID)
    {
        // Assurez-vous que l'utilisateur existe
        $user = User::where('uuid', $userUUID)->first();

        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        // Obtenez le rôle de l'utilisateur
        $role = $user->roles->first(); // Supposons que l'utilisateur ait un seul rôle, ajustez selon votre logique

        if (!$role) {
            return response()->json(['error' => 'Aucun rôle trouvé pour cet utilisateur'], 404);
        }

        // Retournez le UUID du rôle
        // return response()->json(['role_uuid' => $role->uuid]);
        return response()->json(['role_uuid' => $role->uuid]);
    }



public function updateProfile(Request $request, $uuid)
{
    // Vérifier si l'utilisateur est connecté
    $current_user = user_api();

    if (!$current_user) {
        return response()->json([
            'success' => false,
            'message' => 'Vous devez être connecté pour mettre à jour vos informations de base.'
        ], 401); // Code de statut HTTP 401 pour non autorisé
    }

    // Validation des données entrantes
    $validatedData = $request->validate([
        'name' => 'required|string',
        'prenom' => 'required|string',
        'email' => ['required', 'email', 'string', Rule::unique('users')->ignore($current_user->id, "id")],
        'adresse' => 'string',
        'telephone' => 'required|string|min:8',
        'sexe' => 'required|string',
    ]);

    // Rechercher l'utilisateur par UUID
    $user = $this->userRepositoryEloquent->findByUuidOrFail($uuid)->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Utilisateur non trouvé.'
        ], 404); // Code de statut HTTP 404 pour non trouvé
    }

    // Sanitiser les données
    $userData = [
        'name' => $validatedData['name'],
        'prenom' => $validatedData['prenom'],
        'email' => $validatedData['email'],
        'sexe' => $validatedData['sexe'],
        'telephone' => $validatedData['telephone'],
        'adresse' => $validatedData['adresse'],
    ];

    // Utiliser une transaction pour mettre à jour l'utilisateur et attribuer les rôles
    $item = DB::transaction(function () use ($userData, $user, $validatedData) {
        $role = $this->roleRepositoryEloquent->findByUuid($validatedData['role_id'])->first();

        $item = $this->userRepositoryEloquent->modifier($userData, $user);
        $item->syncRoles([$role->name]);

        return $item;
    });

    // Rafraîchir les données de l'utilisateur
    $item = $item->fresh();

    // Retourner les données mises à jour de l'utilisateur sous forme de réponse JSON
    return response()->json([
        'success' => true,
        'data' => new UserResource($item),
        'message' => 'Informations de base mises à jour avec succès.'
    ]);
}

public function updatePassProfil(Request $request)
{
    // Vérifier si l'utilisateur est connecté
    if (!Auth::check()) {
        return response()->json([
            'success' => false,
            'message' => 'Vous devez être connecté pour mettre à jour votre mot de passe.'
        ], 401); // Code de statut HTTP 401 pour non autorisé
    }

    // Validation des données entrantes
    $validatedData = $request->validate([
        'old_password' => 'required|string',
        'new_password' => 'required|string|min:8|different:old_password|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
        'confirm_password' => 'required|string|same:new_password',
    ]);

    // Récupération de l'utilisateur connecté
    // $user = Auth::user();
    $user = $request->user();

    // Vérification du mot de passe actuel de l'utilisateur
    if (!Hash::check($validatedData['old_password'], $user->password)) {
        return response()->json([
            'success' => false,
            'message' => 'Le mot de passe actuel est incorrect.'
        ], 400);
    }

    // Mise à jour du mot de passe de l'utilisateur
    $user->password = bcrypt($validatedData['new_password']);
    $user->save();

    // Réponse JSON avec un message de succès
    return response()->json([
        'success' => true,
        'message' => 'Mot de passe mis à jour avec succès.'
    ]);
}

public function getCaissiers()
{
    // Récupérer le rôle "caissier"
    $role = Role::where('name', 'Caissier')->first();

    if ($role) {
        // Récupérer les utilisateurs ayant le rôle "caissier"
        $caissiers = User::role($role->name)->get();

        if ($caissiers->isEmpty()) {
            // Aucun utilisateur caissier trouvé
            return response()->json(['message' => 'Aucun utilisateur caissier trouvé.']);
        }

        // Retourner les utilisateurs caissiers au format JSON
        return response()->json($caissiers);
    }

    // Retourner une réponse JSON vide en cas d'absence du rôle
    return response()->json(['message' => 'Le rôle "caissier" n\'existe pas.']);
}



    public function getDoctors()
    {
        try {
            // Rechercher le rôle Doctor avec plusieurs variantes possibles
            // Priorité : Doctor > Médecin > Docteur > medecin
            $roleNames = ['Doctor', 'Médecin', 'Docteur', 'medecin'];
            $role = null;
            
            foreach ($roleNames as $roleName) {
                $role = Role::where('name', $roleName)
                    ->where('guard_name', 'api')
                    ->first();
                if ($role) {
                    Log::info('Rôle médecin trouvé', [
                        'role_name' => $role->name,
                        'role_id' => $role->id
                    ]);
                    break;
                }
            }

            if ($role) {
                // Méthode 1 : Utiliser whereHas avec la relation roles (plus fiable avec Spatie Permission)
                $doctors = User::whereHas('roles', function($query) use ($role) {
                    $query->where('roles.id', $role->id)
                          ->where('roles.guard_name', 'api');
                })
                ->select('id', 'uuid', 'name', 'prenom', 'email', 'telephone')
                ->get();
                    
                // Si aucun résultat avec whereHas, essayer avec la méthode role() de Spatie
                if ($doctors->isEmpty()) {
                    Log::warning('Aucun médecin trouvé avec whereHas, tentative avec role()', [
                        'role_name' => $role->name
                    ]);
                    $doctors = User::role($role->name, 'api')
                        ->select('id', 'uuid', 'name', 'prenom', 'email', 'telephone')
                        ->get();
                }
                    
                Log::info('Médecins récupérés', [
                    'role_name' => $role->name,
                    'role_id' => $role->id,
                    'doctors_count' => $doctors->count()
                ]);
                    
                return reponse_json_transform([
                    'data' => $doctors,
                    'message' => 'Liste des médecins récupérée avec succès'
                ]);
            }

            // Si aucun rôle trouvé, retourner un tableau vide
            Log::warning('Aucun rôle Doctor trouvé pour récupérer les médecins', [
                'searched_names' => $roleNames,
                'available_roles' => Role::where('guard_name', 'api')->pluck('name')->toArray()
            ]);
            return reponse_json_transform([
                'data' => [],
                'message' => 'Aucun rôle médecin trouvé. Veuillez créer un rôle "Doctor" et assigner des utilisateurs.'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des médecins: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return reponse_json_transform([
                'data' => [],
                'message' => 'Erreur lors de la récupération des médecins: ' . $e->getMessage()
            ], 500);
        }
    }
}
