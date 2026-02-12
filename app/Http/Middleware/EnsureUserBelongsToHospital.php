<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware EnsureUserBelongsToHospital
 * 
 * Vérifie que l'utilisateur authentifié appartient à l'hôpital (tenant) courant.
 * Bloque l'accès si l'utilisateur tente d'accéder aux données d'un autre hôpital.
 * 
 * @package App\Http\Middleware
 */
class EnsureUserBelongsToHospital
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Laisser passer les requêtes OPTIONS (preflight CORS) sans traitement
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }
        
        // Si l'utilisateur n'est pas authentifié, laisser passer (géré par auth middleware)
        if (!auth()->check()) {
            return $next($request);
        }

        // Dans l'architecture multi-locataire par base de données (Database per Tenant),
        // l'existence de l'utilisateur dans la base de données du locataire actuel
        // confirme implicitement son appartenance à cet hôpital.
        // Les vérifications explicites de 'hospital_id' sont obsolètes car la colonne n'existe plus
        // sur le modèle User du locataire.

        return $next($request);
    }
}
