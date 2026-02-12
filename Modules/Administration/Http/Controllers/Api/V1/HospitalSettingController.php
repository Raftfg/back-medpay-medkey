<?php

namespace Modules\Administration\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\HospitalSettingsService;
use Modules\Administration\Http\Controllers\AdministrationController;
use Modules\Administration\Entities\HospitalSetting;
use Modules\Administration\Http\Requests\Api\V1\StoreHospitalSettingRequest;
use Modules\Administration\Http\Requests\Api\V1\UpdateHospitalSettingRequest;
use Modules\Administration\Http\Requests\Api\V1\UpdateManyHospitalSettingsRequest;
use Illuminate\Support\Facades\Gate;

/**
 * Contrôleur HospitalSettingController
 * 
 * Gère les paramètres de configuration par hôpital via l'API.
 * Assure l'isolation multi-tenant : hospital_id est TOUJOURS injecté automatiquement.
 * 
 * @package Modules\Administration\Http\Controllers\Api\V1
 */
class HospitalSettingController extends AdministrationController
{
    /**
     * Service de gestion des paramètres
     *
     * @var HospitalSettingsService
     */
    protected $settingsService;

    /**
     * Constructeur
     *
     * @param HospitalSettingsService $settingsService
     */
    public function __construct(HospitalSettingsService $settingsService)
    {
        parent::__construct();
        $this->settingsService = $settingsService;
    }

    /**
     * Liste tous les paramètres de l'hôpital courant
     * 
     * GET /api/v1/settings
     * GET /api/v1/settings?group=general
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        // Vérifier l'autorisation
        if (Gate::denies('viewAny', HospitalSetting::class)) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $group = $request->get('group');
        
        if ($group) {
            $settings = $this->settingsService->getGroup($group);
        } else {
            $settings = $this->settingsService->all();
        }

        // Transformer la collection pour la réponse
        $data = $settings->map(function ($setting) {
                return [
                    'key' => $setting->key,
                    'value' => $setting->value,
                    'type' => $setting->type,
                    'group' => $setting->group,
                    'description' => $setting->description,
                    'is_public' => $setting->is_public,
                ];
            })->values();

        return reponse_json_transform($data);
    }

    /**
     * Récupère un paramètre spécifique
     *
     * @param string $key
     * @return Response
     */
    public function show(string $key)
    {
        $value = $this->settingsService->get($key);

        if ($value === null) {
            return response()->json([
                'message' => 'Paramètre non trouvé',
            ], 404);
        }

        return reponse_json_transform([
            'key' => $key,
            'value' => $value,
        ]);
    }

    /**
     * Crée ou met à jour un paramètre
     *
     * @param StoreHospitalSettingRequest $request
     * @return Response
     */
    public function store(StoreHospitalSettingRequest $request)
    {
        // Vérifier l'autorisation
        if (Gate::denies('create', HospitalSetting::class)) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $setting = $this->settingsService->set(
            $request->key,
            $request->value,
            $request->type ?? 'string',
            $request->group ?? 'general',
            $request->description ?? null,
            $request->is_public ?? false
        );

        return reponse_json_transform([
            'message' => 'Paramètre enregistré avec succès',
            'data' => [
                'key' => $setting->key,
                'value' => $setting->value,
                'type' => $setting->type,
                'group' => $setting->group,
                'description' => $setting->description,
                'is_public' => $setting->is_public,
            ],
        ], 201);
    }

    /**
     * Met à jour un paramètre spécifique par sa clé
     * 
     * PATCH /api/v1/settings/{key}
     *
     * @param UpdateHospitalSettingRequest $request
     * @param string $key
     * @return Response
     */
    public function update(UpdateHospitalSettingRequest $request, string $key)
    {
        // Vérifier que le paramètre existe
        if (!$this->settingsService->has($key)) {
            return response()->json([
                'message' => 'Paramètre non trouvé',
            ], 404);
        }

        // Récupérer le paramètre existant pour vérifier les permissions
        $existingSetting = HospitalSetting::where('key', $key)->first();
        
        if ($existingSetting && Gate::denies('update', $existingSetting)) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $setting = $this->settingsService->set(
            $key,
            $request->value,
            $request->type ?? $existingSetting->type ?? 'string',
            $request->group ?? $existingSetting->group ?? 'general',
            $request->description ?? $existingSetting->description ?? null,
            $request->is_public ?? $existingSetting->is_public ?? false
        );

        return reponse_json_transform([
            'message' => 'Paramètre mis à jour avec succès',
            'data' => [
                'key' => $setting->key,
                'value' => $setting->value,
                'type' => $setting->type,
                'group' => $setting->group,
                'description' => $setting->description,
                'is_public' => $setting->is_public,
            ],
        ]);
    }

    /**
     * Met à jour plusieurs paramètres en une seule fois
     *
     * @param UpdateManyHospitalSettingsRequest $request
     * @return Response
     */
    public function updateMany(UpdateManyHospitalSettingsRequest $request)
    {
        // Vérifier l'autorisation
        if (Gate::denies('updateMany', HospitalSetting::class)) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $settingsArray = [];
        foreach ($request->settings as $setting) {
            $settingsArray[$setting['key']] = [
                'value' => $setting['value'],
                'type' => $setting['type'] ?? 'string',
                'group' => $setting['group'] ?? 'general',
                'description' => $setting['description'] ?? null,
                'is_public' => $setting['is_public'] ?? false,
            ];
        }

        $this->settingsService->setMany($settingsArray);

        return reponse_json_transform([
            'message' => 'Paramètres mis à jour avec succès',
        ]);
    }

    /**
     * Supprime un paramètre
     *
     * @param string $key
     * @return Response
     */
    public function destroy(string $key)
    {
        $deleted = $this->settingsService->delete($key);

        if (!$deleted) {
            return response()->json([
                'message' => 'Paramètre non trouvé',
            ], 404);
        }

        return reponse_json_transform([
            'message' => 'Paramètre supprimé avec succès',
        ]);
    }

    /**
     * Récupère les paramètres publics (accessibles sans authentification)
     *
     * @return Response
     */
    public function public()
    {
        $settings = $this->settingsService->getPublic();

        return reponse_json_transform($settings);
    }

    /**
     * Récupère les paramètres d'un groupe spécifique
     *
     * @param string $group
     * @return Response
     */
    public function group(string $group)
    {
        $settings = $this->settingsService->getGroup($group);

        return reponse_json_transform($settings);
    }

    /**
     * Upload du logo de l'hôpital
     * 
     * POST /api/v1/settings/upload-logo
     * 
     * @param Request $request
     * @return Response
     */
    public function uploadLogo(Request $request)
    {
        // Vérifier l'autorisation
        if (Gate::denies('create', HospitalSetting::class)) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048', // 2MB max
        ], [
            'logo.required' => 'Le fichier logo est obligatoire.',
            'logo.image' => 'Le fichier doit être une image.',
            'logo.mimes' => 'Le format de l\'image doit être : jpeg, png, jpg, gif, svg ou webp.',
            'logo.max' => 'La taille de l\'image ne doit pas dépasser 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $file = $request->file('logo');
            $hospitalId = currentHospitalId();

            if (!$hospitalId) {
                return response()->json([
                    'message' => 'Hôpital non identifié',
                ], 400);
            }

            // Créer le dossier de stockage pour les logos d'hôpitaux
            $storagePath = "hospitals/{$hospitalId}/logos";
            
            // Générer un nom de fichier unique
            $filename = 'logo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Stocker le fichier
            $path = $file->storeAs($storagePath, $filename, 'public');
            
            // Générer l'URL publique
            $url = Storage::url($path);
            
            // Sauvegarder le chemin dans les paramètres
            $setting = $this->settingsService->set(
                'app.logo',
                $url,
                'string',
                'appearance',
                'Logo de l\'hôpital (upload)',
                true // is_public
            );

            // Supprimer l'ancien logo si différent
            $oldLogo = $this->settingsService->get('app.logo');
            if ($oldLogo && $oldLogo !== $url && strpos($oldLogo, '/storage/hospitals/') !== false) {
                $oldPath = str_replace('/storage/', '', parse_url($oldLogo, PHP_URL_PATH));
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            return reponse_json_transform([
                'message' => 'Logo uploadé avec succès',
                'data' => [
                    'key' => 'app.logo',
                    'value' => $url,
                    'url' => asset($url),
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'upload du logo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erreur lors de l\'upload du logo',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
