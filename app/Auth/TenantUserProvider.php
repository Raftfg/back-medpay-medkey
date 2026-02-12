<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use App\Core\Services\TenantConnectionService;

class TenantUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        // Établir la connexion tenant avant de récupérer l'utilisateur
        $this->setTenantConnection();
        
        return parent::retrieveById($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $this->setTenantConnection();
        
        return parent::retrieveByToken($identifier, $token);
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        $this->setTenantConnection();
        
        return parent::retrieveByCredentials($credentials);
    }

    /**
     * Établir la connexion tenant en utilisant X-Hospital-Id du header ou la connexion déjà établie
     */
    protected function setTenantConnection()
    {
        $tenantService = app(TenantConnectionService::class);
        
        // Vérifier si une connexion tenant est déjà établie (par TenantMiddleware)
        if ($tenantService->isConnected()) {
            // Utiliser la connexion déjà établie
            return;
        }
        
        // Sinon, essayer d'établir la connexion manuellement
        $request = request();
        $hospitalId = $request->header('X-Hospital-Id') ?? $request->get('hospital_id');
        
        // Si pas d'ID dans le header, essayer de récupérer depuis la requête (stocké par TenantMiddleware)
        if (!$hospitalId) {
            $hospitalId = $request->attributes->get('hospital_id');
        }
        
        if ($hospitalId) {
            // Récupérer l'hôpital depuis la base CORE (Hospital est dans la base CORE, pas dans tenant)
            try {
                $hospital = \App\Core\Models\Hospital::on('core')->active()->find($hospitalId);
                
                if ($hospital) {
                    $tenantService->connect($hospital);
                }
            } catch (\Exception $e) {
                \Log::error("Erreur lors de la récupération de l'hôpital dans TenantUserProvider", [
                    'hospital_id' => $hospitalId,
                    'error' => $e->getMessage()
                ]);
            }
        } else {
            // En dernier recours, essayer d'identifier par domaine (comme TenantMiddleware)
            $domain = $request->header('X-Original-Host') 
                ?? $request->header('X-Tenant-Domain') 
                ?? $request->get('tenant_domain')
                ?? $request->getHost();
            
            // Extraire le domaine sans le port
            $domainParts = explode(':', $domain);
            $domain = $domainParts[0];
            
            // Chercher l'hôpital par domaine dans la base CORE
            try {
                $hospital = \App\Core\Models\Hospital::on('core')->where('domain', $domain)->first();
                
                if ($hospital) {
                    $tenantService->connect($hospital);
                } else {
                    // En développement, utiliser le premier hôpital actif comme fallback
                    if (app()->environment(['local', 'testing'])) {
                        $hospital = \App\Core\Models\Hospital::on('core')->active()->first();
                        if ($hospital) {
                            $tenantService->connect($hospital);
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Erreur lors de la recherche de l'hôpital par domaine dans TenantUserProvider", [
                    'domain' => $domain,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
