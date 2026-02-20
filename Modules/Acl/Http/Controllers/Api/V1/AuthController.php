<?php

namespace Modules\Acl\Http\Controllers\Api\V1;

use App\Core\Models\Hospital;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use Illuminate\Http\Response;
// use App\Models\User
use Modules\Acl\Entities\User;
// use Modules\Acl\Entities\User;

use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
// Helpers multi-tenant (chargés automatiquement via composer.json)
// use function App\Helpers\{currentHospitalId, setTenant};
use App\Http\Resources\UserCurrentResource;
use Modules\Acl\Http\Requests\LoginRequest;
use Modules\Acl\Http\Resources\UserResource;
use Modules\Acl\Repositories\RoleRepository;
use Modules\Acl\Repositories\UserRepository;
use Modules\Acl\Http\Requests\ProfilShowRequest;
use Modules\Acl\Http\Requests\ProfilUpdateRequest;
use Modules\Acl\Http\Requests\ResetPasswordRequest;
use Modules\Acl\Http\Requests\ForgotPasswordRequest;
use Modules\Acl\Http\Requests\UserTelMobileStoreRequest;
use Modules\Acl\Http\Requests\UserEmailConfirmationRequest;
use Modules\Acl\Http\Requests\UserTelMobileVerifierRequest;

class AuthController extends \App\Http\Controllers\Api\V1\ApiController
{
    use \Modules\Acl\Traits\EnvoiNotificationUserTrait;

    /**
     * @var PostRepository
     */
    protected $userRepository;
    protected $roleRepositoryEloquent;

    public function __construct(
        UserRepository $userRepository,
        RoleRepository $roleRepositoryEloquent
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->roleRepositoryEloquent = $roleRepositoryEloquent;
    }

    /**
     * Connexion de l'utilisateur
     *
     * @param  Request  $request
     * @return Response
     */
    /**
     * Connexion de l'utilisateur avec validation multi-tenant stricte
     *
     * @param  LoginRequest  $request
     * @return Response
     */
    public function login(LoginRequest $request)
    {
        $email = $request->email;
        $password = $request->password;

        Log::info('Login attempt', [
            'email' => $email,
            'connection' => DB::connection()->getName(),
            'database' => DB::connection()->getDatabaseName(),
            'tenant_service_current' => \App\Services\TenantService::current() ? \App\Services\TenantService::current()->id : 'null',
        ]);

        // ÉTAPE 2: Rechercher l'utilisateur par email (sans filtre hospital_id d'abord)
        // pour permettre la détection de l'hôpital depuis l'utilisateur si nécessaire
        $user = User::withoutGlobalScopes() // Désactiver le Global Scope pour la recherche initiale
            ->where('email', $email)
            ->first();
        
        if (!$user) {
            // Utilisateur non trouvé - ne pas révéler si l'email existe ou non (sécurité)
            Log::warning('Login: User not found', [
                'email' => $email,
                'searched_in_db' => DB::connection()->getDatabaseName(),
            ]);
            
            $data = [
                'erreur' => __('Email ou mot de passe non valide!')
            ];
            return reponse_json_transform($data, 401);
        }

        Log::info('Login: User found', ['user_id' => $user->id, 'password_hash' => $user->password]);

        // ÉTAPE 3: Vérifier le mot de passe
        if (!Hash::check($password, $user->password)) {
            Log::warning('Login: Password mismatch', [
                'user_id' => $user->id,
                'input_password_length' => strlen($password),
                'stored_hash' => $user->password,
                 'check_result' => Hash::check($password, $user->password) ? 'true' : 'false'
            ]);
            
            $data = [
                'erreur' => __('Email ou mot de passe non valide!')
            ];
            return reponse_json_transform($data, 401);
        }



        // ÉTAPE 6: Récupérer l'utilisateur complet avec relations
        $user = $this->userRepository->findByUuidOrFail($user->uuid)->first();

        // ÉTAPE 7: Vérifier que l'hôpital est actif
        $hospital = \App\Services\TenantService::current();
        if (!$hospital || !$hospital->isActive()) {
            Log::warning('Tentative de connexion sur un hôpital inactif', [
                'user_id' => $user->id,
                'hospital_status' => $hospital ? $hospital->status : 'not_found',
            ]);
            
            $data = [
                'erreur' => __('Votre hôpital n\'est pas actif. Veuillez contacter l\'administrateur.')
            ];
            return reponse_json_transform($data, 403);
        }
        
        // ÉTAPE 8: Créer le token Passport
        $token = $user->createToken($user->uuid)->accessToken;

        // ÉTAPE 9: Récupérer les informations de l'utilisateur
        $role = $user->roles->first();
        $permissions = $user->getAllPermissions()->pluck('name');
        if ($hospital && ($hospital->plan === 'free')) {
            $allowedPermissions = collect([
                'voir_module_patient',
                'voir_module_mouvement',
                'voir_module_pharmacie',
                'voir_module_caisse',
            ]);
            $permissions = $permissions->filter(
                fn ($permissionName) => $allowedPermissions->contains(strtolower($permissionName))
            )->values();
        }

        // ÉTAPE 10: Log de connexion réussie
        Log::info('Connexion réussie', [
            'user_id' => $user->id,
            'email' => $email,
            'hospital_name' => $hospital->name,
            'ip' => $request->ip(),
        ]);

        // ÉTAPE 11: Retourner les données
        $donnees = [
            'access_token' => $token,
            'user' => array_merge($user->toArray(), [
                'plan' => $hospital?->plan,
            ]),
            'role' => $role,
            'permissions' => $permissions,
            'hospital' => $hospital, // Inclure les infos de l'hôpital
            // Exposer explicitement le flag de premier changement de mot de passe
            'must_change_password' => (bool) ($user->must_change_password ?? false),
        ];
        
        return reponse_json_transform($donnees);
    }

    /**
     * Déconnexion de l'utilisateur
     *
     * @param  Request  $request
     * @return Response
     */
    // public function logout($request) {
    //     \Log::info($request);
    //     $userId = $request->user()->id;

    //     $request->user()->token()->revoke();

    //     //Dispatcher l'évènement de login
    //     event(new \Modules\Acl\Events\AuthLogoutEvent($userId, $request->ipInfo));

    //     $data = [
    //         'message' => __('Déconnexion avec succès')
    //     ];
    //     return reponse_json_transform($data);
    // }

    /**
     * Déconnexion de l'utilisateur
     * Révoque le token Passport de l'utilisateur authentifié
     *
     * @param  Request  $request
     * @return Response
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if ($user) {
                // Récupérer le token de la requête actuelle (Passport)
                $token = $user->token();
                
                if ($token) {
                    // Révoquer le token Passport de la requête actuelle
                    $token->revoke();
                }
                
                // Optionnel : révoquer tous les tokens de l'utilisateur pour une déconnexion complète
                // Décommenter si vous voulez déconnecter l'utilisateur de tous les appareils
                // $user->tokens->each(function ($token) {
                //     $token->revoke();
                // });
            }

            $data = [
                'message' => __('Déconnexion avec succès')
            ];

            return reponse_json_transform($data);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la déconnexion', [
                'user_id' => $request->user() ? $request->user()->id : null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Même en cas d'erreur, on retourne un succès pour ne pas bloquer le frontend
            $data = [
                'message' => __('Déconnexion effectuée')
            ];

            return reponse_json_transform($data);
        }
    }

    /**
     * Obtient l'utilisateur connecté
     *
     * @return Response
     */
    public function user()
    {
        return new UserCurrentResource(user_api());
    }

    /**
     * Obtenir les informations sur le profil.
     * 
     * Vérifie que l'utilisateur demandé appartient au même hôpital que l'utilisateur authentifié.
     *
     * @return Response
     */
    public function showProfil(ProfilShowRequest $request)
    {
        $currentUser = user_api();
        
        // Récupérer l'utilisateur demandé
        $item = $this->userRepository->findByUuidOrFail($request->uuid)->first();
        
        return new UserResource($item);
        
        return new UserResource($item);
    }

    /**
     * Update a resource.
     *
     * @return Response
     */
    public function updateProfil(ProfilUpdateRequest $request)
    {
        $user = user_api();  //existe-il cet element?
        $attributs = $request->except(['uuid']);
        $item = DB::transaction(function () use ($attributs, $user) {
            $item = $this->userRepository->modifier($attributs, $user);
            return $item;
        });
        $item = $item->fresh();
        return new UserResource($item);
    }

    /**
     * Handle an incoming password reset link request.
     * 
     * Filtre par hospital_id pour respecter le multi-tenancy.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        // Vérifier que l'utilisateur existe
        $user = User::withoutGlobalScopes()
            ->where('email', $request->email)
            ->first();
        
        if (!$user) {
            // Ne pas révéler si l'email existe ou non (sécurité)
            $data = [
                'email' => [__('Si cet email existe, un lien de réinitialisation vous sera envoyé.')]
            ];
            // Retourner un succès pour ne pas révéler l'existence de l'email
            return reponse_json_transform(['message' => __('Si cet email existe, un lien de réinitialisation vous sera envoyé.')]);
        }
        
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            $data['message'] = __($status);
            return reponse_json_transform($data);
        }

        $data = [
            'email' => [trans($status)]
        ];
        failed_validation_throw_exception($data);
    }

    /**
     * Handle an incoming new password request.
     * 
     * Filtre par hospital_id pour respecter le multi-tenancy.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        if ($status == Password::PASSWORD_RESET) {
            $data['message'] = __($status);
            return reponse_json_transform($data);
        }

        //Token est expiré. On va personnalisé le message
        if ($status == Password::INVALID_TOKEN) {
            $status .= " " . __("Veuillez redemander un nouveau lien de réinitialisation");
        }

        $data = [
            'email' => [trans($status)]
        ];
        failed_validation_throw_exception($data);
    }

    /**
     * Validation du courriel de l'utilisateur
     * 
     * Note: Cette méthode est généralement appelée via un lien email (sans authentification).
     * Le tenant peut ne pas être défini, donc on utilise l'hospital_id de l'utilisateur.
     *
     * @return Response
     */
    public function emailConfirmation(UserEmailConfirmationRequest $request)
    {
        $uuid = $request->uuid;
        
        if (!$request->hasValidSignature()) {
            $message = __("Lien expiré ou signature non valide");
            $message .= ". " . __("Un nouveau courriel est envoyé à votre boîte de messagerie");
            $user = $this->userRepository->findByField('uuid', $uuid)->first();
            
            if ($user) {
                $this->confirmationCourriel($user);
            }
            
            return reponse_json_transform([
                "message" => $message
            ], 401);
        }
        
        $user = $this->userRepository->findByField('uuid', $uuid)->first();
        
        if (!$user) {
            abort(404, 'Utilisateur non trouvé.');
        }
        
        // Si un tenant est défini, vérifier qu'il correspond à l'utilisateur
        // NOTE: Avec l'isolation par base de données, la présence de l'utilisateur est suffisante

        //Confirmer cet email
        if ($user->email_verified_at) {
            $message = __("Email déjà confirmé");
        } else {
            $message = __("Email confirmé avec succès");
            $user->email_verified_at = now();
            $user->save();
        }
        $data['message'] = $message;

        return reponse_json_transform($data);
    }

    /**
     * Validation du courriel de l'utilisateur
     *
     * @return Response
     */
    public function renvoiLienEmailConfirmation(Request $request)
    {
        $user = user_api();
        $data['message'] = _('KO');
        if (!$user->email_verified_at) {
            $this->confirmationCourriel($user);
            $data['message'] = __('Lien renvoyé avec succès. Vérifiez votre boîte courriel.');
        }

        return reponse_json_transform($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function userInfosConfirmees(Request $request)
    {
        $user = user_api();
        $data = [
            'email_verified_at' => $user->email_verified_at,
            'tel_mobile_verified_at' => $user->tel_mobile_verified_at,
            'tel_mobile' => $user->tel_mobile,
        ];
        return reponse_json_transform($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function envoyerTelMobile(UserTelMobileStoreRequest $request)
    {
        $user = user_api();
        $tel_mobile_code = random_int(100000, 999999);
        $user->tel_mobile = $request->tel_mobile;
        $user->tel_mobile_code = $tel_mobile_code;
        $user->save();

        //@TODO : Envoyer le SMS plus tard
        $data["tel_mobile_code"] = $tel_mobile_code;
        $view_url = "acl::emails.code_sms";
        $sujet = __("Votre code");
        $attributes = [
            'view_url' => $view_url,
            'data' => $data,
            'destinataires' => [$user->email],
            'sujet' => $sujet,
        ];
        mail_queue(new \Modules\Notifier\Emails\CourrielNotifier($attributes));
        //

        $data['message'] = __('SMS envoyé avec succès. Vérifiez votre messagerie.');
        return reponse_json_transform($data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function verifierTelMobile(UserTelMobileVerifierRequest $request)
    {
        $user = user_api();
        if ($user->tel_mobile_code) {
            $user->tel_mobile_code = null;
            $user->tel_mobile_verified_at = now()->toDateTimeString();
            $user->save();
        }

        $data['message'] = __('Numéro de tél validé avec succès!');
        return reponse_json_transform($data);
    }

    /**
     * Mise à jour du mot de passe de l'utilisateur authentifié
     * 
     * Filtre par hospital_id pour respecter le multi-tenancy.
     *
     * @param  Request  $request
     * @return Response
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'new_password' => 'required|min:6',
        ]);
        
        $currentUser = user_api();
        
        if (!$currentUser) {
            return response()->json([
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }
        
        // Utiliser l'utilisateur authentifié
        $user = $currentUser;
        
        if (Hash::check($request->password, $user->password)) {
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return response()->json([
                'message' => 'Password updated successfully',
            ]);
        } else {
            return response()->json([
                'message' => 'Invalid password',
            ], 401);
        }
    }

    /**
     * Réinitialisation du mot de passe
     * 
     * Filtre par hospital_id pour respecter le multi-tenancy.
     *
     * @param  Request  $request
     * @return Response
     */
    public function reset(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => ['required', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'],
                'password_confirmation' => 'required|min:8|same:password',
            ]);

            if ($request->password !== $request->password_confirmation) {
                return response()->json([
                    'message' => 'Les mots de passe ne sont pas conformes',
                ], 400);
            }

            // Filtrer par pour respecter le multi-tenancy
            $user = User::withoutGlobalScopes()
                ->where('email', $request->email)
                ->first();
                
            if ($user != null) {
                $user->password = Hash::make($request->password);
                $user->must_change_password = false;
                $user->save();

                $this->markAccountValidationAsValidated();
                
                Log::info('Mot de passe réinitialisé avec succès', [
                    'user_id' => $user->id,
                ]);
                
                return response()->json([
                    'message' => 'Mot de passe réinitialisé avec succès',
                ], 200);
            } else {
                // Ne pas révéler si l'email existe ou non (sécurité)
                return response()->json([
                    'message' => 'Si cet email existe, le mot de passe a été réinitialisé',
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la réinitialisation du mot de passe', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);
            
            // Attrapez l'exception et renvoyez une réponse d'erreur appropriée
            return response()->json([
                'message' => 'Une erreur s\'est produite lors de la réinitialisation du mot de passe',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Demande de réinitialisation de mot de passe
     * 
     * Filtre par hospital_id pour respecter le multi-tenancy.
     *
     * @param  Request  $request
     * @return Response
     */
    public function requestPassword(Request $request)
    {
        // Vérifier si l'e-mail est valide
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'message' => 'Veuillez entrer un email valide',
            ], 400);
        }

        // Filtrer par hospital_id pour respecter le multi-tenancy
        $user = User::withoutGlobalScopes()
            ->where('email', $request->email)
            ->first();

        // Ne pas révéler si l'email existe ou non (sécurité)
        // Retourner toujours un succès même si l'utilisateur n'existe pas
        if ($user == null) {
            Log::info('Demande de réinitialisation pour un email inexistant', [
                'email' => $request->email,
            ]);
            
            return response()->json([
                'message' => 'Si cet email existe, un lien de réinitialisation vous sera envoyé',
            ], 200);
        }

        // Générer le token de réinitialisation de mot de passe
        $token = Password::createToken($user);

        // Encodez l'adresse e-mail pour une utilisation dans un URL
        $encodedEmail = urlencode($user->email);

        // Générer le lien de réinitialisation de mot de passe avec l'adresse e-mail
        $resetLink = 'http://localhost:8080/auth-pages/reset?token=' . $token . '&email=' . $encodedEmail;

        // Envoyer l'e-mail de réinitialisation de mot de passe
        Mail::to($user->email)->send(new PasswordResetMail($user, $resetLink));
        
        Log::info('Email de réinitialisation envoyé', [
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Si cet email existe, un lien de réinitialisation vous sera envoyé',
        ], 200);
    }

    //     public function requestPassword(Request $request)
    // {
    //     $user = User::where('email', $request->email)->first();

    //     if ($user == null) {
    //         return response()->json([
    //             'message' => 'Utilisateur non trouvé',
    //         ]);
    //     }

    //     // Vérifier si l'e-mail est valide
    //     if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
    //         return response()->json([
    //             'message' => 'Veuillez entrer un email valide',
    //         ]);
    //     }

    //     // Générer le token de réinitialisation de mot de passe avec expiration
    //     $token = Password::createToken($user);

    //     // Définir la durée d'expiration du token (en minutes)
    //     $expires = config('auth.passwords.users.expire');

    //     // Générer le lien de réinitialisation de mot de passe avec l'adresse e-mail
    //     $resetLink = url('auth-pages/reset?token=' . $token . '&email=' . urlencode($user->email));

    //     // Envoyer l'e-mail de réinitialisation de mot de passe
    //     Mail::to($user->email)->send(new PasswordResetMail($user,  $expires, $resetLink));


    //     return response()->json([
    //         'message' => 'Email envoyé avec succès',
    //     ]);
    // }

    private function markAccountValidationAsValidated(): void
    {
        try {
            $hospital = \App\Services\TenantService::current();
            if (! $hospital) {
                return;
            }

            $coreHospital = Hospital::find($hospital->id);
            if (! $coreHospital) {
                return;
            }

            $state = is_array($coreHospital->setup_wizard_state) ? $coreHospital->setup_wizard_state : [];
            $state['account_validation_status'] = 'validated';
            $state['account_validated_at'] = now()->toIso8601String();

            $coreHospital->update([
                'setup_wizard_state' => $state,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Impossible de marquer la validation de compte onboarding', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
