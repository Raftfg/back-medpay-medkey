<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Core\Models\Hospital;
use App\Core\Services\TenantConnectionService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware TenantMiddleware
 * 
 * Détecte automatiquement le tenant (hôpital) à partir du domaine de la requête.
 * Stocke l'hôpital dans la requête et la session pour un accès facile dans toute l'application.
 * 
 * @package App\Http\Middleware
 */
class TenantMiddleware
{
    /**
     * Routes exclues de la détection du tenant
     * (ex: routes d'administration globale, health checks, routes publiques d'authentification, etc.)
     * 
     * @var array
     */
    protected $excludedRoutes = [
        'health',
        'api/health',
        'api/v1/public/tenants/*',
        'admin/*', // Routes d'administration globale si nécessaire
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // PRIORITÉ 1: Laisser passer les requêtes OPTIONS (preflight CORS) sans AUCUN traitement
        // Le middleware CORS gérera ces requêtes - IMPORTANT: Ne pas toucher à ces requêtes
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }
        
        // PRIORITÉ 2: Vérifier si la route est exclue (routes publiques, login, etc.)
        if ($this->isExcludedRoute($request)) {
            return $next($request);
        }

        // Si la table core.hospitals est indisponible, éviter un 500 SQL et retourner un message clair.
        if (!$this->isHospitalsTableAvailable()) {
            return response()->json([
                'message' => "Le service tenant n'est pas initialisé (table core.hospitals indisponible).",
                'hint' => 'Exécutez les migrations core avant de continuer.',
            ], 503);
        }

        // Récupérer le domaine de la requête
        $domain = $this->getDomainFromRequest($request);
        
        Log::debug("TenantMiddleware - Domaine extrait", [
            'domain' => $domain,
            'host' => $request->getHost(),
            'full_url' => $request->fullUrl(),
        ]);

        // NOUVEAUTÉ : Identifier par ID d'hôpital (via header) - Très utile pour le dev local
        $hospitalId = $request->header('X-Hospital-Id') ?: $request->input('hospital_id');
        $hospital = null;

        if ($hospitalId) {
            $hospital = Hospital::find($hospitalId);
            if ($hospital) {
                Log::debug("Hôpital identifié par ID (Header/Input)", ['hospital_id' => $hospital->id]);
            }
        }

        // Si non trouvé par ID, tenter par domaine (comportement standard)
        if (!$hospital) {
            $hospital = $this->identifyHospital($domain);
            if ($hospital) {
                Log::debug("Hôpital identifié par domaine", [
                    'hospital_id' => $hospital->id,
                    'hospital_name' => $hospital->name,
                    'domain' => $domain
                ]);
            }
        }

        // Si aucun hôpital trouvé par domaine
        if (!$hospital) {
            // PRIORITÉ 1: Utiliser l'hospital_id de l'utilisateur authentifié (respecte le multi-tenancy)
            if (auth()->check() && auth()->user()->hospital_id) {
                $hospital = Hospital::find(auth()->user()->hospital_id);
                
                if ($hospital) {
                    Log::info("Utilisation de l'hôpital de l'utilisateur authentifié", [
                        'hospital_id' => $hospital->id,
                        'hospital_name' => $hospital->name,
                        'user_id' => auth()->id(),
                        'domain' => $domain,
                    ]);
                }
            }
            
            // PRIORITÉ 2: En développement local uniquement, utiliser le premier hôpital actif comme fallback
            // (uniquement si l'utilisateur n'est pas authentifié ou n'a pas d'hospital_id)
            if (!$hospital && app()->environment(['local', 'testing'])) {
                // FALLBACK CIBLÉ : Utiliser "Hopital CentralMA" pour le développement
                $hospital = Hospital::where('domain', 'hopital-centralma-plateforme.com')->first();
                
                // Si non trouvé, alors prendre le premier actif
                if (!$hospital) {
                    $hospital = Hospital::active()->first();
                }
                
                if ($hospital) {
                    Log::warning("Utilisation de l'hôpital par défaut en développement (fallback)", [
                        'hospital_id' => $hospital->id,
                        'hospital_name' => $hospital->name,
                        'domain' => $domain,
                        'note' => 'L\'utilisateur n\'est pas authentifié ou n\'a pas d\'hospital_id',
                    ]);
                } else {
                    return $this->handleUnknownDomain($request, $domain);
                }
            } elseif (!$hospital) {
                // En production, bloquer l'accès si aucun hôpital trouvé
                return $this->handleUnknownDomain($request, $domain);
            }
        } else {
            // Si un hôpital est trouvé par domaine, vérifier qu'il correspond à l'utilisateur (si authentifié)
            if (auth()->check() && auth()->user()->hospital_id) {
                if ($hospital->id !== auth()->user()->hospital_id) {
                    Log::warning("Conflit entre le domaine et l'hospital_id de l'utilisateur", [
                        'domain_hospital_id' => $hospital->id,
                        'user_hospital_id' => auth()->user()->hospital_id,
                        'user_id' => auth()->id(),
                        'domain' => $domain,
                    ]);
                    
                    // En développement, on bloque aussi pour montrer l'isolation au DG
                    abort(403, "Le domaine ne correspond pas à l'hôpital de votre session. Veuillez vous déconnecter.");
                    
                    // (L'ancien code reconnectait automatiquement à l'hôpital de l'utilisateur, ce qui masquait l'isolation)
                }
            }
        }

        // Vérifier que l'hôpital est actif
        if (!$hospital->isActive()) {
            return $this->handleInactiveHospital($request, $hospital);
        }

        // PHASE 2: Basculer la connexion DB vers la base du tenant
        try {
            $tenantService = app(TenantConnectionService::class);
            $tenantService->connect($hospital);
            
            Log::debug("Connexion tenant établie", [
                'hospital_id' => $hospital->id,
                'hospital_name' => $hospital->name,
                'database' => $hospital->database_name,
            ]);
        } catch (\Exception $e) {
            Log::error("Échec de la connexion à la base tenant", [
                'hospital_id' => $hospital->id,
                'hospital_name' => $hospital->name,
                'database' => $hospital->database_name,
                'error' => $e->getMessage(),
            ]);
            
            return $this->handleConnectionError($request, $hospital, $e);
        }

        // Stocker l'hôpital dans la requête et la session
        $this->setTenant($request, $hospital);

        return $next($request);
    }

    /**
     * Récupère le domaine de la requête
     * 
     * Supporte :
     * - Sous-domaines (ex: hopital1.ma-plateforme.com)
     * - Header X-Tenant-Domain
     * - Paramètre tenant_domain
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function getDomainFromRequest(Request $request): string
    {
        // PRIORITÉ 1: Header personnalisé X-Original-Host (pour développement local)
        // Utilisé quand le frontend pointe vers localhost:8000 mais veut préserver le domaine original
        if ($request->hasHeader('X-Original-Host')) {
            $originalHost = $request->header('X-Original-Host');
            // Extraire le domaine (sans le port si c'est 8080)
            $parts = explode(':', $originalHost);
            return $parts[0];
        }

        // PRIORITÉ 2: Header personnalisé X-Tenant-Domain
        if ($request->hasHeader('X-Tenant-Domain')) {
            return $request->header('X-Tenant-Domain');
        }

        // PRIORITÉ 3: Paramètre de requête tenant_domain
        if ($request->has('tenant_domain')) {
            return $request->get('tenant_domain');
        }

        // PRIORITÉ 4: Récupérer le host (ex: hopital1.ma-plateforme.com ou localhost)
        $host = $request->getHost();

        // En développement local, on peut utiliser un header personnalisé
        // ou un paramètre de requête pour simuler le domaine
        if (app()->environment(['local', 'testing'])) {
            // Option: Sous-domaine local
            if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
                $parts = explode('.', $host);
                if (count($parts) > 1 && $parts[0] !== 'localhost' && $parts[0] !== '127') {
                    $subdomain = $parts[0];
                    
                    // Pour le dev local, on retourne le host complet (ex: hopital1.localhost)
                    // Cela correspondra au champ 'domain' en base de données.
                    return $host; 
                }
            }
        }

        return $host;
    }

    /**
     * Identifie l'hôpital à partir du domaine
     *
     * @param  string  $domain
     * @return \App\Core\Models\Hospital|null
     */
    protected function identifyHospital(string $domain): ?Hospital
    {
        // Utiliser le cache pour améliorer les performances
        $cacheKey = "hospital_by_domain_{$domain}";

        try {
            return Cache::remember($cacheKey, 3600, function () use ($domain) {
                // Rechercher par domaine exact
                $hospital = Hospital::where('domain', $domain)->first();
                
                // Si non trouvé, rechercher par slug
                if (!$hospital) {
                    $slug = $this->extractSlugFromDomain($domain);
                    if ($slug) {
                        $hospital = Hospital::where('slug', $slug)->first();
                    }
                }

                // Log pour debug
                if (!$hospital) {
                    Log::debug("Hôpital non trouvé pour le domaine", [
                        'domain' => $domain,
                        'domaines_disponibles' => Hospital::pluck('domain')->toArray()
                    ]);
                }
                
                return $hospital;
            });
        } catch (\Throwable $e) {
            Log::error("TenantMiddleware: impossible d'identifier l'hôpital", [
                'domain' => $domain,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Vérifie la disponibilité de la table core.hospitals.
     */
    protected function isHospitalsTableAvailable(): bool
    {
        try {
            return Schema::connection('core')->hasTable('hospitals');
        } catch (\Throwable $e) {
            Log::error("TenantMiddleware: table core.hospitals indisponible", [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Extrait le slug du domaine (pour la recherche alternative)
     *
     * @param  string  $domain
     * @return string|null
     */
    protected function extractSlugFromDomain(string $domain): ?string
    {
        // Exemple: hopital1.ma-plateforme.com -> hopital1
        $parts = explode('.', $domain);
        return $parts[0] ?? null;
    }

    /**
     * Stocke le tenant dans la requête et la session
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Core\Models\Hospital  $hospital
     * @return void
     */
    protected function setTenant(Request $request, Hospital $hospital): void
    {
        // Stocker dans la requête (accessible via $request->hospital_id)
        $request->merge(['hospital_id' => $hospital->id]);
        $request->attributes->set('hospital', $hospital);
        $request->attributes->set('hospital_id', $hospital->id);

        // Stocker dans la session (pour les requêtes suivantes)
        if ($request->hasSession()) {
            $request->session()->put('hospital_id', $hospital->id);
            $request->session()->put('hospital', $hospital->toArray());
        }

        // Définir une variable globale accessible via app('hospital')
        app()->instance('hospital', $hospital);
        app()->instance('hospital_id', $hospital->id);
    }

    /**
     * Gère le cas d'un domaine inconnu
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $domain
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleUnknownDomain(Request $request, string $domain): Response
    {
        // En développement, on peut permettre l'accès avec un message d'avertissement
        if (app()->environment(['local', 'testing'])) {
            Log::warning("Tenant inconnu: {$domain}", [
                'domain' => $domain,
                'host' => $request->getHost(),
                'ip' => $request->ip(),
            ]);

            // Optionnel: Créer un hôpital par défaut en développement
            // ou rediriger vers une page de configuration
            if (config('app.allow_unknown_tenants_in_dev', false)) {
                return response()->json([
                    'message' => "Domaine inconnu: {$domain}. Veuillez configurer cet hôpital.",
                    'domain' => $domain,
                ], 404);
            }
        }

        // En production, bloquer l'accès
        abort(404, "Domaine non reconnu: {$domain}");
    }

    /**
     * Gère le cas d'un hôpital inactif
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Core\Models\Hospital  $hospital
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleInactiveHospital(Request $request, Hospital $hospital): Response
    {
        $message = match($hospital->status) {
            'suspended' => "L'accès à cet hôpital a été suspendu. Veuillez contacter l'administrateur.",
            'inactive' => "Cet hôpital n'est pas actif. Veuillez contacter l'administrateur.",
            default => "L'accès à cet hôpital n'est pas autorisé.",
        };

        abort(403, $message);
    }

    /**
     * Gère les erreurs de connexion à la base tenant
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Core\Models\Hospital  $hospital
     * @param  \Exception  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleConnectionError(Request $request, Hospital $hospital, \Exception $exception): Response
    {
        // En développement, donner plus de détails
        if (app()->environment(['local', 'testing'])) {
            return response()->json([
                'message' => "Impossible de se connecter à la base de données de l'hôpital.",
                'hospital' => $hospital->name,
                'database' => $hospital->database_name,
                'error' => $exception->getMessage(),
                'hint' => 'Vérifiez que la base de données existe et est accessible.',
            ], 503);
        }

        // En production, message générique
        return response()->json([
            'message' => "Service temporairement indisponible. Veuillez réessayer plus tard.",
        ], 503);
    }

    /**
     * Vérifie si la route est exclue de la détection du tenant
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isExcludedRoute(Request $request): bool
    {
        $path = $request->path();

        // Vérifier les patterns de routes exclues
        foreach ($this->excludedRoutes as $pattern) {
            if (Str::is($pattern, $path)) {
                return true;
            }
        }

        // Exclure les routes d'authentification publique (login, register, etc.)
        // Ces routes nécessitent maintenant un tenant pour l'authentification
        // $publicAuthRoutes = [
        //     'api/v1/login',
        //     'api/v1/register',
        //     'api/v1/request-password',
        //     'api/v1/reset-password',
        //     'api/v1/email-confirmation',
        // ];

        // foreach ($publicAuthRoutes as $route) {
        //     if (Str::contains($path, $route)) {
        //         return true;
        //     }
        // }

        return false;
    }
}
