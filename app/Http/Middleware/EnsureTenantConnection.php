<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Core\Services\TenantConnectionService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware EnsureTenantConnection
 * 
 * Vérifie que la connexion tenant est active avant de continuer.
 * Bloque l'accès si la connexion n'est pas établie.
 * 
 * @package App\Http\Middleware
 */
class EnsureTenantConnection
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
        // Laisser passer les requêtes OPTIONS (preflight CORS)
        if ($request->isMethod('OPTIONS')) {
            return $next($request);
        }

        // Récupérer le service de connexion tenant
        $tenantService = app(TenantConnectionService::class);

        // Vérifier si une connexion tenant est active
        if (!$tenantService->isConnected()) {
            Log::warning("Tentative d'accès sans connexion tenant active", [
                'path' => $request->path(),
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);

            // En développement, donner plus de détails
            if (app()->environment(['local', 'testing'])) {
                return response()->json([
                    'message' => "Aucune connexion tenant active.",
                    'hint' => 'Le TenantMiddleware doit être exécuté avant ce middleware.',
                    'path' => $request->path(),
                ], 503);
            }

            // En production, message générique
            return response()->json([
                'message' => "Service temporairement indisponible.",
            ], 503);
        }

        // Vérifier que la connexion est valide
        try {
            $connection = $tenantService->getCurrentConnection();
            
            if (!$connection) {
                throw new \Exception("Connexion tenant invalide");
            }

            // Tester la connexion avec une requête simple
            $connection->getPdo();
            
        } catch (\Exception $e) {
            Log::error("Connexion tenant invalide ou perdue", [
                'error' => $e->getMessage(),
                'path' => $request->path(),
            ]);

            // En développement, donner plus de détails
            if (app()->environment(['local', 'testing'])) {
                return response()->json([
                    'message' => "La connexion tenant a été perdue ou est invalide.",
                    'error' => $e->getMessage(),
                    'hint' => 'Vérifiez que la base de données tenant existe et est accessible.',
                ], 503);
            }

            // En production, message générique
            return response()->json([
                'message' => "Service temporairement indisponible.",
            ], 503);
        }

        return $next($request);
    }
}
