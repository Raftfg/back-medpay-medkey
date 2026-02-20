<?php

namespace App\Core\Services;

use App\Core\Models\Hospital;
use App\Mail\AccountValidationMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
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

        $payload = [
            'name' => 'Admin',
            'prenom' => $hospital->name,
            'telephone' => $adminPhone,
            'password' => Hash::make($tempPassword),
            'must_change_password' => true,
            'status' => 'active',
        ];

        // Certaines bases tenant historiques n'ont pas toutes les colonnes (ex: status).
        // On n'envoie que les champs réellement présents pour éviter les SQLSTATE 42S22.
        $safePayload = [];
        foreach ($payload as $column => $value) {
            if (Schema::connection('tenant')->hasColumn('users', $column)) {
                $safePayload[$column] = $value;
            }
        }

        $user = User::updateOrCreate(
            ['email' => $adminEmail],
            $safePayload
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
            $resetLink = $this->buildFrontendResetLink($hospital, $token, $encodedEmail);

            Mail::to($user->email)->send(new AccountValidationMail($user, $resetLink));
            $this->updateAccountValidationState($hospital, 'sent', [
                'account_validation_sent_at' => now()->toIso8601String(),
            ]);
        } catch (\Throwable $e) {
            Log::error("TenantAdminService: impossible d'envoyer l'email d'activation", [
                'hospital_id' => $hospital->id,
                'email' => $adminEmail,
                'error' => $e->getMessage(),
            ]);
            $this->updateAccountValidationState($hospital, 'failed', [
                'account_validation_failed_at' => now()->toIso8601String(),
            ]);
            throw $e;
        } finally {
            $this->tenantConnectionService->disconnect();
        }
    }

    /**
     * Construit l'URL front de création / réinitialisation du mot de passe.
     */
    private function buildFrontendResetLink(Hospital $hospital, string $token, string $encodedEmail): string
    {
        $baseUrl = rtrim((string) config('premier.frontend.url.racine', 'http://localhost:8080'), '/');
        if (!str_starts_with($baseUrl, 'http://') && !str_starts_with($baseUrl, 'https://')) {
            $baseUrl = 'https://' . $baseUrl;
        }

        $parsed = parse_url($baseUrl);
        $scheme = $parsed['scheme'] ?? 'http';
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $path = $parsed['path'] ?? '';
        $tenantFrontendBase = "{$scheme}://{$hospital->domain}{$port}{$path}";

        return rtrim($tenantFrontendBase, '/') . "/auth-pages/reset?token={$token}&email={$encodedEmail}&validation=1";
    }

    private function updateAccountValidationState(Hospital $hospital, string $status, array $extra = []): void
    {
        $hospital->refresh();
        $state = is_array($hospital->setup_wizard_state) ? $hospital->setup_wizard_state : [];
        $state['account_validation_status'] = $status;
        foreach ($extra as $key => $value) {
            $state[$key] = $value;
        }

        $hospital->update([
            'setup_wizard_state' => $state,
        ]);
    }
}

