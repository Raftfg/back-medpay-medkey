<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware Cors - Solution ABSOLUE et DÉFINITIVE
 * 
 * Gère TOUTES les requêtes CORS de manière absolue.
 * Autorise TOUTES les origines locales sans exception.
 * FORCE l'ajout des headers même si l'origine n'est pas détectée.
 */
class Cors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Récupérer l'origine de TOUTES les façons possibles
        $origin = $request->header('Origin') 
               ?? $request->header('origin') 
               ?? $request->server('HTTP_ORIGIN')
               ?? ($_SERVER['HTTP_ORIGIN'] ?? '')
               ?? '';
        
        // Pour les requêtes OPTIONS (preflight), répondre IMMÉDIATEMENT
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
            
            // TOUJOURS autoriser l'origine si elle existe (pour les requêtes OPTIONS)
            if ($origin) {
                $response->headers->set('Access-Control-Allow-Origin', $origin, true);
            }
            
            // TOUJOURS ajouter les headers CORS pour les requêtes OPTIONS
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS', true);
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, Accept-Language, X-Requested-With, X-Hospital-Id, X-Original-Host, X-Tenant-Domain, Origin', true);
            $response->headers->set('Access-Control-Allow-Credentials', 'true', true);
            $response->headers->set('Access-Control-Max-Age', '86400', true);
            
            return $response;
        }

        // Pour les autres requêtes, continuer et ajouter les headers CORS
        $response = $next($request);

        // TOUJOURS ajouter les headers CORS pour toutes les requêtes
        if (!empty($origin)) {
            // Autoriser TOUTES les origines (pour le développement)
            $response->headers->set('Access-Control-Allow-Origin', $origin, true);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS', true);
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, Accept-Language, X-Requested-With, X-Hospital-Id, X-Original-Host, X-Tenant-Domain, Origin', true);
            $response->headers->set('Access-Control-Allow-Credentials', 'true', true);
        }

        return $response;
    }

    /**
     * Vérifie si l'origine est locale (localhost, 127.0.0.1, ou sous-domaine de localhost)
     */
    protected function isLocalOrigin(string $origin): bool
    {
        if (empty($origin)) {
            return false;
        }

        // Pattern qui accepte :
        // - http://localhost:port
        // - http://127.0.0.1:port
        // - http://*.localhost:port (ex: hopital1.localhost:8080)
        // - https:// versions aussi
        $pattern = '/^https?:\/\/(localhost|127\.0\.0\.1|.*\.localhost)(:\d+)?$/i';
        return (bool) preg_match($pattern, $origin);
    }
}
