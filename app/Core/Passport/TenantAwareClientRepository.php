<?php

namespace App\Core\Passport;

use Laravel\Passport\ClientRepository as PassportClientRepository;
use Laravel\Passport\Passport;
use RuntimeException;

/**
 * ClientRepository multi-tenant : en contexte tenant, si l'ID configuré
 * (PASSPORT_PERSONAL_ACCESS_CLIENT_ID) n'existe pas dans la base courante,
 * on résout le client depuis oauth_personal_access_clients pour éviter
 * "Attempt to read property \"secret\" on null".
 */
class TenantAwareClientRepository extends PassportClientRepository
{
    /**
     * @return \Laravel\Passport\Client
     *
     * @throws RuntimeException
     */
    public function personalAccessClient()
    {
        if ($this->personalAccessClientId) {
            $client = $this->find($this->personalAccessClientId);
            if ($client !== null) {
                return $client;
            }
            // ID configuré absent en base courante (ex. tenant) : résoudre depuis la table
        }

        $model = Passport::personalAccessClient();

        if (! $model->exists()) {
            throw new RuntimeException('Personal access client not found. Please create one.');
        }

        $first = $model->orderBy($model->getKeyName(), 'desc')->first();
        if ($first === null || $first->client === null) {
            throw new RuntimeException('Personal access client not found. Please create one.');
        }

        return $first->client;
    }
}
