<?php

namespace Modules\Acl\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use Modules\Acl\Entities\User;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // Dans l'architecture database-per-tenant, on est déjà connecté à la base tenant
        // Récupérer l'hôpital courant depuis le service tenant
        $tenantService = app(\App\Core\Services\TenantConnectionService::class);
        $hospital = $tenantService->getCurrentHospital();
        
        if (!$hospital) {
            $this->command->warn('⚠️  Aucun hôpital courant détecté.');
            return;
        }

        // Créer un admin pour cet hôpital avec email unique
        $email = 'admin@' . str_replace(['.', ' '], ['', ''], strtolower($hospital->domain));
        
        $user = User::updateOrCreate(
            [
                'email' => $email,
            ],
            [
                'name' => 'Admin',
                'prenom' => $hospital->name,
                'password' => Hash::make('MotDePasse'),
                'email_verified_at' => now()->toDateTimeString(),
            ]
        );
        
        if (method_exists($user, 'hasRole') && !$user->hasRole('Admin', 'api')) {
            $adminRole = \Spatie\Permission\Models\Role::where(['name' => 'Admin', 'guard_name' => 'api'])->first();
            if ($adminRole) {
                $user->assignRole($adminRole);
            }
        }

        // Synchroniser explicitement toutes les permissions à l'administrateur
        try {
            if (method_exists($user, 'syncPermissions')) {
                $allPermissions = \Spatie\Permission\Models\Permission::where(['guard_name' => 'api'])->pluck('name')->toArray();
                $user->syncPermissions($allPermissions);
            }
        } catch (\Throwable $e) {
            // Ignorer silencieusement si Spatie n'est pas encore initialisé lors de certains seeds
        }

        $this->command->info('✅ Utilisateur admin créé pour ' . $hospital->name . '.');
    }
}
