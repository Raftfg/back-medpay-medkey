<?php

namespace App\Core\Jobs;

use App\Core\Models\Hospital;
use App\Core\Services\TenantAdminService;
use App\Core\Services\TenantProvisioningService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job de provisioning d'un nouveau tenant (hôpital) créé via l'API d'onboarding.
 *
 * - Crée la base de données (idempotent)
 * - Exécute les migrations
 * - Active les modules par défaut
 * - Exécute les seeders de base
 * - Met à jour les statuts d'onboarding
 */
class ProvisionNewTenant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected int $hospitalId;

    /**
     * Crée une nouvelle instance de job.
     */
    public function __construct(int $hospitalId)
    {
        $this->hospitalId = $hospitalId;
    }

    /**
     * Exécute le job.
     */
    public function handle(
        TenantProvisioningService $provisioningService,
        TenantAdminService $tenantAdminService
    ): void
    {
        /** @var Hospital|null $hospital */
        $hospital = Hospital::find($this->hospitalId);

        if (!$hospital) {
            Log::warning('ProvisionNewTenant: hôpital introuvable', [
                'hospital_id' => $this->hospitalId,
            ]);
            return;
        }

        Log::info('ProvisionNewTenant: démarrage du provisioning', [
            'hospital_id' => $hospital->id,
            'hospital_name' => $hospital->name,
            'database_name' => $hospital->database_name,
        ]);

        // Marquer l'onboarding comme en cours
        $hospital->update([
            'onboarding_status' => 'provisioning',
        ]);

        try {
            $options = [
                'create_database' => true,
                'run_migrations' => true,
                'activate_default_modules' => true,
                'run_seeders' => true,
                'force' => false,
                // Important : ne pas échouer si la base existe déjà
                'skip_if_exists' => true,
            ];

            $provisioningService->provision($hospital, $options);

            // Créer / mettre à jour l'admin hôpital dans la base tenant
            $adminResult = $tenantAdminService->createOrUpdateAdmin(
                $hospital,
                $hospital->email,
                $hospital->phone
            );

            $hospital->refresh();

            $hospital->update([
                'status' => 'active',
                'provisioned_at' => $hospital->provisioned_at ?? now(),
                'onboarding_status' => 'provisioned',
            ]);

            Log::info('ProvisionNewTenant: provisioning terminé avec succès', [
                'hospital_id' => $hospital->id,
                'hospital_name' => $hospital->name,
                'admin_id' => $adminResult['user']->id ?? null,
            ]);
        } catch (Exception $e) {
            Log::error('ProvisionNewTenant: erreur lors du provisioning', [
                'hospital_id' => $hospital->id,
                'hospital_name' => $hospital->name,
                'error' => $e->getMessage(),
            ]);

            $hospital->update([
                'onboarding_status' => 'failed',
            ]);

            // Laisser l'exception remonter dans le log de la queue, mais ne pas la relancer ici
        }
    }
}

