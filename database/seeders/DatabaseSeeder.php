<?php

namespace Database\Seeders;
use App\Models\ModePayement;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(ModePayementSeeder::class);
        // S'assurer que les rôles/permissions et l'utilisateur par défaut existent
        $this->call(\Modules\Acl\Database\Seeders\AclDatabaseSeeder::class);

        $this->call(\Modules\Administration\Database\Seeders\InsuranceTableSeeder::class);
    }
}
