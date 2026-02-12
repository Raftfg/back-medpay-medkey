<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Models\Hospital;
use App\Http\Controllers\Controller;
use App\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Backend minimal pour le Setup Wizard (first login).
 *
 * - Récupérer l'état courant du wizard
 * - Sauvegarder les étapes principales (infos hôpital, modules, langue)
 * - Marquer le wizard comme complété
 */
class OnboardingWizardController extends Controller
{
    /**
     * Récupère l'état courant du wizard pour l'hôpital courant.
     */
    public function getState(Request $request): JsonResponse
    {
        /** @var Hospital|null $hospital */
        $hospital = TenantService::current();

        if (!$hospital) {
            return response()->json([
                'message' => 'No current hospital in context',
            ], 400);
        }

        return response()->json([
            'data' => [
                'hospital_id' => $hospital->id,
                'uuid' => $hospital->uuid,
                'status' => $hospital->status,
                'onboarding_status' => $hospital->onboarding_status,
                'setup_wizard_state' => $hospital->setup_wizard_state ?? [
                    'step' => 1,
                    'completed_steps' => [],
                ],
                'setup_wizard_completed_at' => optional($hospital->setup_wizard_completed_at)->toIso8601String(),
            ],
        ]);
    }

    /**
     * Sauvegarde l'étape "Informations de l'hôpital".
     */
    public function saveHospitalInfo(Request $request): JsonResponse
    {
        /** @var Hospital|null $hospital */
        $hospital = TenantService::current();

        if (!$hospital) {
            return response()->json([
                'message' => 'No current hospital in context',
            ], 400);
        }

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|nullable|string|max:255',
            'country' => 'sometimes|nullable|string|max:100',
            'city' => 'sometimes|nullable|string|max:100',
            'phone' => 'sometimes|nullable|string|max:50',
        ]);

        // Mise à jour CORE
        $hospital->fill($data);
        $hospital->save();

        $state = $hospital->setup_wizard_state ?? ['step' => 1, 'completed_steps' => []];
        $state['completed_steps'] = array_values(array_unique(array_merge(
            $state['completed_steps'] ?? [],
            ['hospital_info']
        )));
        $state['step'] = max($state['step'] ?? 1, 2);

        $hospital->setup_wizard_state = $state;
        $hospital->save();

        return response()->json([
            'data' => [
                'setup_wizard_state' => $state,
            ],
        ]);
    }

    /**
     * Sauvegarde l'étape "Langue principale".
     */
    public function saveLanguage(Request $request): JsonResponse
    {
        /** @var Hospital|null $hospital */
        $hospital = TenantService::current();

        if (!$hospital) {
            return response()->json([
                'message' => 'No current hospital in context',
            ], 400);
        }

        $data = $request->validate([
            'main_language' => 'required|string|max:10',
        ]);

        $hospital->main_language = $data['main_language'];
        $hospital->save();

        $state = $hospital->setup_wizard_state ?? ['step' => 1, 'completed_steps' => []];
        $state['completed_steps'] = array_values(array_unique(array_merge(
            $state['completed_steps'] ?? [],
            ['language']
        )));
        $state['step'] = max($state['step'] ?? 1, 3);

        $hospital->setup_wizard_state = $state;
        $hospital->save();

        return response()->json([
            'data' => [
                'setup_wizard_state' => $state,
            ],
        ]);
    }

    /**
     * Marque le wizard comme complété.
     */
    public function complete(Request $request): JsonResponse
    {
        /** @var Hospital|null $hospital */
        $hospital = TenantService::current();

        if (!$hospital) {
            return response()->json([
                'message' => 'No current hospital in context',
            ], 400);
        }

        $state = $hospital->setup_wizard_state ?? [];
        $state['completed'] = true;
        $state['step'] = max($state['step'] ?? 1, 99);
        $state['completed_steps'] = array_values(array_unique(array_merge(
            $state['completed_steps'] ?? [],
            ['hospital_info', 'language']
        )));

        $hospital->setup_wizard_state = $state;
        $hospital->setup_wizard_completed_at = now();
        $hospital->onboarding_status = 'completed';
        $hospital->save();

        return response()->json([
            'data' => [
                'setup_wizard_state' => $state,
                'setup_wizard_completed_at' => $hospital->setup_wizard_completed_at->toIso8601String(),
            ],
        ]);
    }
}

