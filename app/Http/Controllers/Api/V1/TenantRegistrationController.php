<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Jobs\ProvisionNewTenant;
use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Acl\Entities\User;

/**
 * API publique d'onboarding des hôpitaux (tenants).
 *
 * 1) Inscription d'un hôpital
 * 2) Consultation du statut d'onboarding
 */
class TenantRegistrationController extends Controller
{
    /**
     * Inscription d'un nouveau tenant (hôpital).
     *
     * - Crée l'entrée dans la base CORE
     * - Lance un job asynchrone de provisioning
     */
    public function register(Request $request): JsonResponse
    {
        if (! $this->isCoreReady()) {
            return response()->json([
                'message' => "Le noyau multi-tenant n'est pas initialisé (table core.hospitals indisponible).",
                'hint' => 'Exécutez les migrations core: php artisan migrate --database=core --path=database/core/migrations',
            ], 503);
        }

        // Compatibilité:
        // - nouveau front: email + organization_name + plan=free
        // - ancien front: hospital_name + admin_email + autres champs
        $normalizedPayload = [
            'hospital_name' => $request->input('organization_name', $request->input('hospital_name')),
            'admin_email' => $request->input('email', $request->input('admin_email')),
            'admin_phone' => $request->input('admin_phone'),
            'country' => $request->input('country'),
            'city' => $request->input('city'),
            'main_language' => $request->input('main_language', 'fr'),
            'plan' => $request->input('plan', 'free'),
        ];

        $validator = Validator::make($normalizedPayload, [
            'hospital_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_phone' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'main_language' => 'nullable|string|max:10',
            'plan' => 'required|string|in:free,trial,standard,premium',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $baseSlug = Str::slug($data['hospital_name']);
        if ($baseSlug === '') {
            $baseSlug = 'tenant';
        }

        // Créer l'hôpital dans CORE
        $hospital = Hospital::create([
            'name' => $data['hospital_name'],
            // Valeurs provisoires, remplacées immédiatement après création
            'domain' => $baseSlug . '-pending.' . $this->extractRootDomain(),
            'slug' => $baseSlug . '-pending-' . Str::lower(Str::random(6)),
            'database_name' => config('tenant.database_prefix', 'medkey_') . $baseSlug . '_' . Str::lower(Str::random(4)),
            'database_host' => config('database.connections.mysql.host'),
            'database_port' => config('database.connections.mysql.port'),
            'database_username' => config('database.connections.mysql.username'),
            'database_password' => config('database.connections.mysql.password'),
            'status' => 'provisioning',
            'plan' => $data['plan'],
            'address' => $this->buildAddress($data['city'] ?? null, $data['country'] ?? null),
            'country' => $data['country'],
            'city' => $data['city'],
            'phone' => $data['admin_phone'],
            'email' => $data['admin_email'],
            'main_language' => $data['main_language'],
            'onboarding_status' => 'pending',
            'setup_wizard_state' => [
                'account_validation_status' => 'pending',
            ],
        ]);

        $this->assignFinalTenantIdentifiers($hospital, $baseSlug);

        // Lancer le provisioning en arrière-plan
        ProvisionNewTenant::dispatch($hospital->id);

        return response()->json([
            'data' => $this->formatTenantResponse($hospital),
        ], 201);
    }

    /**
     * Construit l'adresse à partir de la ville et du pays.
     */
    private function buildAddress(?string $city, ?string $country): ?string
    {
        $parts = array_values(array_filter([$city, $country], fn($item) => !empty($item)));
        return empty($parts) ? null : implode(', ', $parts);
    }

    /**
     * Récupère le statut d'onboarding d'un hospital via son UUID.
     */
    public function status(string $uuid): JsonResponse
    {
        if (! $this->isCoreReady()) {
            return response()->json([
                'message' => "Le noyau multi-tenant n'est pas initialisé (table core.hospitals indisponible).",
                'hint' => 'Exécutez les migrations core: php artisan migrate --database=core --path=database/core/migrations',
            ], 503);
        }

        $hospital = Hospital::where('uuid', $uuid)->first();

        if (!$hospital) {
            return response()->json([
                'message' => 'Hospital not found',
            ], 404);
        }

        $autologinUrl = null;
        if ($hospital->onboarding_status === 'provisioned') {
            $autologinUrl = $this->buildFrontendAutoLoginUrl($hospital, $this->issueAutoLoginToken($hospital));
        }

        return response()->json([
            'data' => $this->formatTenantResponse($hospital, $autologinUrl),
        ]);
    }

    /**
     * Consomme un token d'auto-login one-time et retourne une session prête pour Vue.
     */
    public function consumeAutologinToken(Request $request, TenantConnectionService $tenantConnectionService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $token = (string) $request->input('token');
        $cacheKey = $this->autologinCacheKey($token);
        $cached = Cache::pull($cacheKey);

        if (! is_array($cached) || empty($cached['hospital_id'])) {
            return response()->json([
                'message' => "Le lien d'auto-connexion est expiré ou invalide.",
            ], 401);
        }

        $hospital = Hospital::find($cached['hospital_id']);
        if (! $hospital) {
            return response()->json([
                'message' => "L'hôpital associé à ce lien est introuvable.",
            ], 404);
        }

        if (! in_array($hospital->status, ['active', 'provisioning'], true)) {
            return response()->json([
                'message' => "L'espace de cet hôpital n'est pas actif.",
            ], 403);
        }

        $previousDefaultConnection = Config::get('database.default');

        try {
            $tenantConnectionService->connect($hospital);
            // Important: Passport (oauth_clients) utilise la connexion DB par défaut.
            // En autologin public, on force la connexion tenant pour éviter les accès CORE.
            Config::set('database.default', 'tenant');

            $user = User::withoutGlobalScopes()
                ->where('email', $hospital->email)
                ->first();

            if (! $user) {
                $user = User::withoutGlobalScopes()->orderBy('id')->first();
            }

            if (! $user) {
                return response()->json([
                    'message' => "Aucun utilisateur n'est disponible pour la connexion automatique.",
                ], 404);
            }

            if (! Schema::connection('tenant')->hasTable('oauth_clients')) {
                return response()->json([
                    'message' => "La table oauth_clients est absente dans la base tenant. Le provisioning OAuth n'est pas terminé.",
                    'hint' => "Exécuter les migrations Passport sur la base tenant.",
                ], 503);
            }

            $accessToken = $user->createToken($user->uuid)->accessToken;
            $role = $user->roles->first();
            $permissions = $user->getAllPermissions()->pluck('name');

            if ($hospital->plan === 'free') {
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

            return response()->json([
                'data' => [
                    'access_token' => $accessToken,
                    'user' => array_merge($user->toArray(), [
                        'plan' => $hospital->plan,
                    ]),
                    'role' => $role,
                    'permissions' => $permissions,
                    'hospital' => $hospital,
                    'must_change_password' => (bool) ($user->must_change_password ?? false),
                    'redirect_to' => '/home',
                ],
            ]);
        } finally {
            Config::set('database.default', $previousDefaultConnection);
            $tenantConnectionService->disconnect();
        }
    }

    /**
     * Vérifie que la base CORE est prête pour l'onboarding tenant.
     */
    private function isCoreReady(): bool
    {
        try {
            return Schema::connection('core')->hasTable('hospitals');
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function assignFinalTenantIdentifiers(Hospital $hospital, string $baseSlug): void
    {
        $uuidCompact = str_replace('-', '', strtolower((string) $hospital->uuid));
        $uuidShort = substr($uuidCompact, 0, 8);
        $rootDomain = $this->extractRootDomain();
        $dbPrefix = config('tenant.database_prefix', 'medkey_');

        $candidateSlug = trim($baseSlug . '-' . $uuidShort, '-');
        $slug = $candidateSlug;
        $index = 1;

        while (
            Hospital::where('slug', $slug)->where('id', '!=', $hospital->id)->exists() ||
            Hospital::where('domain', $slug . '.' . $rootDomain)->where('id', '!=', $hospital->id)->exists() ||
            Hospital::where('database_name', $this->makeDatabaseName($dbPrefix, $slug))->where('id', '!=', $hospital->id)->exists()
        ) {
            $index++;
            $slug = $candidateSlug . '-' . $index;
        }

        $hospital->update([
            'slug' => $slug,
            'domain' => $slug . '.' . $rootDomain,
            'database_name' => $this->makeDatabaseName($dbPrefix, $slug),
        ]);
    }

    private function makeDatabaseName(string $prefix, string $slug): string
    {
        $normalized = Str::slug($slug, '_');
        $maxDbNameLength = 64;
        $available = max(1, $maxDbNameLength - strlen($prefix));
        $trimmed = substr($normalized, 0, $available);

        return $prefix . $trimmed;
    }

    private function extractRootDomain(): string
    {
        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        if ($appHost) {
            return $appHost;
        }

        $pattern = (string) config('tenant.domain_pattern', '{tenant}.medkey.com');
        if (str_contains($pattern, '{tenant}.')) {
            return str_replace('{tenant}.', '', $pattern);
        }

        return 'medkey.com';
    }

    private function formatTenantResponse(Hospital $hospital, ?string $autologinUrl = null): array
    {
        return [
            'hospital_id' => $hospital->id,
            'uuid' => $hospital->uuid,
            'domain' => $hospital->domain,
            'status' => $hospital->status,
            'onboarding_status' => $hospital->onboarding_status,
            'provisioned_at' => optional($hospital->provisioned_at)->toIso8601String(),
            'frontend_base_url' => $this->buildFrontendBaseUrl($hospital),
            'frontend_login_url' => $this->buildFrontendLoginUrl($hospital),
            'frontend_dashboard_url' => $this->buildFrontendDashboardUrl($hospital),
            'login_url' => $this->buildFrontendLoginUrl($hospital),
            'autologin_url' => $autologinUrl,
            'account_validation_status' => $this->accountValidationStatus($hospital),
        ];
    }

    private function accountValidationStatus(Hospital $hospital): string
    {
        $state = $hospital->setup_wizard_state;
        if (! is_array($state)) {
            return 'pending';
        }

        return (string) ($state['account_validation_status'] ?? 'pending');
    }

    private function buildFrontendBaseUrl(Hospital $hospital): string
    {
        $frontRoot = rtrim((string) config('premier.frontend.url.racine', config('app.frontend_url', 'http://localhost:8080')), '/');
        $parsed = parse_url($frontRoot);

        $scheme = $parsed['scheme'] ?? 'http';
        $host = $hospital->domain;
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $path = $parsed['path'] ?? '';

        return "{$scheme}://{$host}{$port}{$path}";
    }

    private function buildFrontendLoginUrl(Hospital $hospital): string
    {
        return rtrim($this->buildFrontendBaseUrl($hospital), '/') . '/auth-pages/login';
    }

    private function buildFrontendDashboardUrl(Hospital $hospital): string
    {
        return rtrim($this->buildFrontendBaseUrl($hospital), '/') . '/home';
    }

    private function buildFrontendAutoLoginUrl(Hospital $hospital, string $token): string
    {
        return rtrim($this->buildFrontendBaseUrl($hospital), '/') . '/auth-pages/autologin?token=' . urlencode($token);
    }

    private function issueAutoLoginToken(Hospital $hospital): string
    {
        $token = Str::random(72);
        Cache::put($this->autologinCacheKey($token), [
            'hospital_id' => $hospital->id,
            'issued_at' => now()->toIso8601String(),
        ], now()->addMinutes(10));

        return $token;
    }

    private function autologinCacheKey(string $token): string
    {
        return 'tenant_autologin_token:' . hash('sha256', $token);
    }
}

