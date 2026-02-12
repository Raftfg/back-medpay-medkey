<?php

namespace Modules\Acl\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Modules\Acl\Entities\Permission;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer les rôles principaux
        $roles = [
            'Super' => 'Super administrateur',
            'Admin' => 'Administrateur',
            'Agent' => 'Agent',
            'Client' => 'Client',
        ];

        foreach ($roles as $roleName => $displayName) {
            Role::firstOrCreate(
                [
                    'name' => $roleName,
                    'guard_name' => 'api',
                ],
                [
                    'uuid' => (string) Str::uuid(),
                ]
            );
        }

        // Attribuer toutes les permissions aux rôles Admin et Super
        $allPermissions = Permission::where(['guard_name' => 'api'])->pluck('name')->toArray();
        
        $superRole = Role::where(['name' => 'Super', 'guard_name' => 'api'])->first();
        if ($superRole) {
            $superRole->syncPermissions($allPermissions);
        }

        $adminRole = Role::where(['name' => 'Admin', 'guard_name' => 'api'])->first();
        if ($adminRole) {
            $adminRole->syncPermissions($allPermissions);
        }
    }
}

