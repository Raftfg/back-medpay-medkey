<?php

namespace App\Http\Middleware;

use App\Core\Services\ModuleService;
use App\Core\Services\TenantConnectionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour vérifier qu'un module est activé pour le tenant actuel
 * 
 * Ce middleware doit être utilisé sur les routes des modules pour s'assurer
 * que le module est activé avant d'autoriser l'accès.
 * 
 * Usage dans les routes :
 * Route::middleware(['tenant', 'module:Patient'])->get(...)
 * 
 * @package App\Http\Middleware
 */
class EnsureModuleEnabled
{
    protected ModuleService $moduleService;
    protected TenantConnectionService $tenantConnectionService;

    public function __construct(ModuleService $moduleService, TenantConnectionService $tenantConnectionService)
    {
        $this->moduleService = $moduleService;
        $this->tenantConnectionService = $tenantConnectionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $moduleName  Nom du module requis (passé comme paramètre)
     */
    public function handle(Request $request, Closure $next, ?string $moduleName = null): Response
    {
        // Si aucun nom de module n'est spécifié, extraire depuis la route
        if (!$moduleName) {
            $moduleName = $this->extractModuleNameFromRoute($request);
        }

        if (!$moduleName) {
            // Si on ne peut pas déterminer le module, on autorise l'accès
            // (pour éviter de bloquer des routes non-modulaires)
            return $next($request);
        }

        // Récupérer l'hôpital actuel
        $hospital = $this->tenantConnectionService->getCurrentHospital();

        if (!$hospital) {
            return response()->json([
                'message' => 'Tenant non identifié.',
                'error' => 'TENANT_NOT_FOUND',
            ], 403);
        }

        // Vérifier si le module est activé
        if (!$this->moduleService->isModuleEnabled($hospital, $moduleName)) {
            return response()->json([
                'message' => "Le module '{$moduleName}' n'est pas activé pour cet hôpital.",
                'error' => 'MODULE_NOT_ENABLED',
                'module' => $moduleName,
            ], 403);
        }

        return $next($request);
    }

    /**
     * Extrait le nom du module depuis la route
     * 
     * @param Request $request
     * @return string|null
     */
    protected function extractModuleNameFromRoute(Request $request): ?string
    {
        $path = $request->path();
        
        // Patterns pour extraire le nom du module depuis l'URL
        // Exemples: /api/patient/... -> Patient, /api/stock/... -> Stock
        if (preg_match('#^api/([^/]+)#', $path, $matches)) {
            $moduleSegment = $matches[1];
            
            // Mapper les segments d'URL aux noms de modules
            $moduleMap = [
                'patient' => 'Patient',
                'patients' => 'Patient',
                'stock' => 'Stock',
                'cash' => 'Cash',
                'payment' => 'Payment',
                'payments' => 'Payment',
                'hospitalization' => 'Hospitalization',
                'movement' => 'Movment',
                'movements' => 'Movment',
                'movments' => 'Movment',
                'medical-services' => 'Medicalservices',
                'medicalservices' => 'Medicalservices',
                'administration' => 'Administration',
                'acl' => 'Acl',
                'absence' => 'Absence',
                'annuaire' => 'Annuaire',
                'dashboard' => 'Dashboard',
                'recouvrement' => 'Recouvrement',
                'remboursement' => 'Remboursement',
                
            ];

            return $moduleMap[strtolower($moduleSegment)] ?? null;
        }

        return null;
    }
}
