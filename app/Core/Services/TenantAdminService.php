<?php

namespace App\Core\Services;

use App\Core\Models\Hospital;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Acl\Entities\User;

/**
 * Service de création d'un administrateur d'hôpital (tenant).
 *
 * Utilisé lors du provisioning initial déclenché par l'onboarding.
 */
class TenantAdminService
{
    protected TenantConnectionService $tenantConnectionService;

    public function __construct(TenantConnectionService $tenantConnectionService)
    {
        $this->tenantConnectionService = $tenantConnectionService;
    }

    /**
     * Crée (ou met à jour) l'admin de l'hôpital dans la base tenant.
     *
     * - Email = email fourni à l'inscription
     * - Mot de passe temporaire généré
     * - Flag must_change_password = true
     *
     * @return array{user: \Modules\Acl\Entities\User, temp_password: string}
     */
    public function createOrUpdateAdmin(Hospital $hospital, string $adminEmail, ?string $adminPhone = null): array
    {
        $this->tenantConnectionService->connect($hospital);

        $tempPassword = Str::random(10);

        $user = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'prenom' => $hospital->name,
                'telephone' => $adminPhone,
                'password' => Hash::make($tempPassword),
                'must_change_password' => true,
                'status' => 'active',
            ]
        );

        // Assigner le rôle ADMIN_HOPITAL si présent
        try {
            if (method_exists($user, 'hasRole') && !$user->hasRole('ADMIN_HOPITAL', 'api')) {
                $adminRole = \Spatie\Permission\Models\Role::where([
                    'name' => 'ADMIN_HOPITAL',
                    'guard_name' => 'api',
                ])->first();

                if ($adminRole) {
                    $user->assignRole($adminRole);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('TenantAdminService: impossible d\'assigner le rôle ADMIN_HOPITAL', [
                'hospital_id' => $hospital->id,
                'error' => $e->getMessage(),
            ]);
        }

        $this->tenantConnectionService->disconnect();

        return [
            'user' => $user,
            'temp_password' => $tempPassword,
        ];
    }
}

