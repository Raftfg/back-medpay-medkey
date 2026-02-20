<?php

namespace App\Core\Services;

use App\Core\Models\Hospital;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Modules\Acl\Entities\Permission;
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

        $isFreePlan = ($hospital->plan === 'free');

        // Assigner le rôle ADMIN_HOPITAL si présent (hors plan free)
        try {
            if (
                !$isFreePlan &&
                method_exists($user, 'hasRole') &&
                !$user->hasRole('ADMIN_HOPITAL', 'api')
            ) {
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

        // Plan gratuit: permissions limitées aux 4 modules visibles dans le dashboard
        if ($isFreePlan && method_exists($user, 'syncPermissions')) {
            $freePlanPermissions = Permission::query()
                ->whereIn('name', [
                    'voir_module_patient',
                    'voir_module_mouvement',
                    'voir_module_pharmacie',
                    'voir_module_caisse',
                ])
                ->pluck('name')
                ->toArray();

            // Ne conserver que les permissions du plan gratuit pour cet utilisateur
            $user->syncPermissions($freePlanPermissions);
        }

        $this->tenantConnectionService->disconnect();

        return [
            'user' => $user,
            'temp_password' => $tempPassword,
        ];
    }

    /**
     * Envoie un email d'activation avec un lien de création de mot de passe.
     */
    public function sendActivationEmail(Hospital $hospital, string $adminEmail): void
    {
        try {
            $this->tenantConnectionService->connect($hospital);

            $user = User::where('email', $adminEmail)->first();
            if (!$user) {
                throw new \RuntimeException("Utilisateur admin introuvable pour l'email {$adminEmail}");
            }

            $token = Password::createToken($user);
            $encodedEmail = urlencode($user->email);
            $resetLink = $this->buildFrontendResetLink($token, $encodedEmail);

            Mail::to($user->email)->send(new PasswordResetMail($user, $resetLink));
        } catch (\Throwable $e) {
            Log::error("TenantAdminService: impossible d'envoyer l'email d'activation", [
                'hospital_id' => $hospital->id,
                'email' => $adminEmail,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } finally {
            $this->tenantConnectionService->disconnect();
        }
    }

    /**
     * Construit l'URL front de création / réinitialisation du mot de passe.
     */
    private function buildFrontendResetLink(string $token, string $encodedEmail): string
    {
        $baseUrl = rtrim((string) config('premier.frontend.url.racine', 'http://localhost:8080'), '/');
        if (!str_starts_with($baseUrl, 'http://') && !str_starts_with($baseUrl, 'https://')) {
            $baseUrl = 'https://' . $baseUrl;
        }

        return "{$baseUrl}/auth-pages/reset?token={$token}&email={$encodedEmail}";
    }
}

