<?php

namespace App\Http\Controllers\Api\V1;

use App\Core\Jobs\ProvisionNewTenant;
use App\Core\Models\Hospital;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * API publique d'onboarding des hôpitaux (tenants).
 *
 * 1) Inscription d'un hôpital
 * 2) Consultation du statut d'onboarding
 */
class TenantRegistrationController extends Controller
{
    /**
     * Inscription d'un nouveau tenant (hôpital).
     *
     * - Crée l'entrée dans la base CORE
     * - Lance un job asynchrone de provisioning
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'hospital_name' => 'required|string|max:255',
            'admin_email' => 'required|email|max:255',
            'admin_phone' => 'required|string|max:50',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'main_language' => 'required|string|max:10',
            'plan' => 'required|string|in:trial,standard,premium',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Générer slug, domaine et nom de base
        $slug = Str::slug($data['hospital_name']);
        $domain = $slug . '.' . parse_url(config('app.url'), PHP_URL_HOST);
        if (!$domain) {
            // Fallback : utiliser pattern tenant
            $pattern = config('tenant.domain_pattern', '{tenant}.medkey.com');
            $domain = str_replace('{tenant}', $slug, $pattern);
        }

        $databaseName = config('tenant.database_prefix', 'medkey_') . Str::slug($data['hospital_name'], '_');

        // Vérifier unicité
        if (Hospital::where('slug', $slug)->exists()) {
            return response()->json([
                'message' => 'Slug already taken for another hospital',
            ], 409);
        }

        if (Hospital::where('domain', $domain)->exists()) {
            return response()->json([
                'message' => 'Domain already taken for another hospital',
            ], 409);
        }

        if (Hospital::where('database_name', $databaseName)->exists()) {
            return response()->json([
                'message' => 'Database name already used for another hospital',
            ], 409);
        }

        // Créer l'hôpital dans CORE
        $hospital = Hospital::create([
            'name' => $data['hospital_name'],
            'domain' => $domain,
            'slug' => $slug,
            'database_name' => $databaseName,
            'database_host' => config('database.connections.mysql.host'),
            'database_port' => config('database.connections.mysql.port'),
            'database_username' => config('database.connections.mysql.username'),
            'database_password' => config('database.connections.mysql.password'),
            'status' => 'provisioning',
            'plan' => $data['plan'],
            'address' => $data['city'] . ', ' . $data['country'],
            'country' => $data['country'],
            'city' => $data['city'],
            'phone' => $data['admin_phone'],
            'email' => $data['admin_email'],
            'main_language' => $data['main_language'],
            'onboarding_status' => 'pending',
        ]);

        // Lancer le provisioning en arrière-plan
        ProvisionNewTenant::dispatch($hospital->id);

        return response()->json([
            'data' => [
                'hospital_id' => $hospital->id,
                'uuid' => $hospital->uuid,
                'domain' => $hospital->domain,
                'onboarding_status' => $hospital->onboarding_status,
            ],
        ], 201);
    }

    /**
     * Récupère le statut d'onboarding d'un hospital via son UUID.
     */
    public function status(string $uuid): JsonResponse
    {
        $hospital = Hospital::where('uuid', $uuid)->first();

        if (!$hospital) {
            return response()->json([
                'message' => 'Hospital not found',
            ], 404);
        }

        return response()->json([
            'data' => [
                'hospital_id' => $hospital->id,
                'uuid' => $hospital->uuid,
                'domain' => $hospital->domain,
                'status' => $hospital->status,
                'onboarding_status' => $hospital->onboarding_status,
                'provisioned_at' => optional($hospital->provisioned_at)->toIso8601String(),
                'login_url' => 'https://' . $hospital->domain,
            ],
        ]);
    }
}

