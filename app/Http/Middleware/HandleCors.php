<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware CORS personnalisé
 * 
 * Gère les requêtes CORS de manière explicite et garantit que les requêtes preflight (OPTIONS)
 * sont correctement traitées AVANT tous les autres middlewares.
 * 
 * @package App\Http\Middleware
 */
class HandleCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Récupérer l'origine de la requête (vérifier plusieurs headers possibles)
        $origin = $request->header('Origin') 
               ?? $request->header('origin') 
               ?? $request->server('HTTP_ORIGIN');

        // Liste des origines autorisées (statiques)
        $allowedOrigins = [
            'http://localhost:8080',
            'http://localhost:8081',
            'http://localhost:3000',
            'http://127.0.0.1:8080',
            'http://127.0.0.1:8081',
            'http://127.0.0.1:3000',
            'http://hopital1.localhost:8080',
            'http://hopital2.localhost:8080',
            'http://hopital3.localhost:8080',
            'http://hopital4.localhost:8080',
        ];

        // Ajouter les origines depuis .env
        if (env('FRONTEND_URL')) {
            $allowedOrigins[] = env('FRONTEND_URL');
        }
        if (env('APP_FRONTEND_URL')) {
            $allowedOrigins[] = env('APP_FRONTEND_URL');
        }

        // Déterminer l'origine autorisée
        $allowedOrigin = null;

        if ($origin) {
            // Vérifier si l'origine est dans la liste statique
            if (in_array($origin, $allowedOrigins)) {
                $allowedOrigin = $origin;
                Log::debug('CORS: Origine autorisée (liste statique)', ['origin' => $origin]);
            }
            // Support dynamique pour *.localhost:8080 (pattern regex)
            elseif (preg_match('/^http:\/\/.*\.localhost:8080$/', $origin)) {
                $allowedOrigin = $origin;
                Log::debug('CORS: Origine autorisée (pattern *.localhost:8080)', ['origin' => $origin]);
            }
            // Support pour localhost:8080 avec n'importe quel sous-domaine
            elseif (preg_match('/^http:\/\/([a-zA-Z0-9\-]+\.)?localhost:8080$/', $origin)) {
                $allowedOrigin = $origin;
                Log::debug('CORS: Origine autorisée (pattern localhost:8080)', ['origin' => $origin]);
            }
            // Support pour 127.0.0.1:8080
            elseif (preg_match('/^http:\/\/127\.0\.0\.1:8080$/', $origin)) {
                $allowedOrigin = $origin;
                Log::debug('CORS: Origine autorisée (127.0.0.1:8080)', ['origin' => $origin]);
            }
        }

        // Si aucune origine n'est autorisée mais qu'on a une origine, l'autoriser en développement
        if (!$allowedOrigin && $origin && app()->environment(['local', 'testing', 'development'])) {
            // En développement, autoriser toutes les origines locales
            if (preg_match('/^http:\/\/(localhost|127\.0\.0\.1|.*\.localhost):\d+$/', $origin)) {
                $allowedOrigin = $origin;
                Log::debug('CORS: Origine autorisée (mode développement)', ['origin' => $origin]);
            }
        }

        // Gérer les requêtes preflight (OPTIONS)
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
            Log::debug('CORS: Requête OPTIONS (preflight)', ['origin' => $origin]);
        } else {
            $response = $next($request);
        }

        // Ajouter les headers CORS à la réponse (pour toutes les requêtes, y compris OPTIONS)
        if ($allowedOrigin) {
            // Utiliser response()->header() pour garantir que les headers sont bien ajoutés
            $response->headers->set('Access-Control-Allow-Origin', $allowedOrigin, true);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS', true);
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-Tenant-Domain, X-Hospital-Id, X-Original-Host, Accept, Origin, Accept-Language', true);
            $response->headers->set('Access-Control-Allow-Credentials', 'true', true);
            $response->headers->set('Access-Control-Max-Age', '86400', true);
            
            // Headers exposés
            $response->headers->set('Access-Control-Expose-Headers', 'Authorization, Content-Type', true);
            
            Log::debug('CORS: Headers ajoutés à la réponse', [
                'origin' => $origin,
                'allowed_origin' => $allowedOrigin,
                'method' => $request->method(),
                'url' => $request->fullUrl(),
            ]);
        } else {
            // En cas d'absence d'origine autorisée, logger pour debug
            // MAIS en développement, on autorise quand même pour éviter les blocages
            if (app()->environment(['local', 'testing', 'development']) && $origin) {
                // En développement, autoriser l'origine même si elle n'est pas dans la liste
                $response->headers->set('Access-Control-Allow-Origin', $origin, true);
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS', true);
                $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-Tenant-Domain, X-Hospital-Id, X-Original-Host, Accept, Origin, Accept-Language', true);
                $response->headers->set('Access-Control-Allow-Credentials', 'true', true);
                $response->headers->set('Access-Control-Max-Age', '86400', true);
                $response->headers->set('Access-Control-Expose-Headers', 'Authorization, Content-Type', true);
                
                Log::warning('CORS: Origine autorisée en mode développement (non dans la liste)', [
                    'origin' => $origin,
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ]);
            } else {
                Log::warning('CORS: Origine non autorisée', [
                    'origin' => $origin,
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'environment' => app()->environment(),
                ]);
            }
        }

        return $response;
    }
}
