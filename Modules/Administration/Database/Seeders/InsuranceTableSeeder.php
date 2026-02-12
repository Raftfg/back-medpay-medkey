<?php

namespace Modules\Administration\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;


use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Modules\Administration\Entities\Insurance;
use Modules\Acl\Entities\User;
use Illuminate\Support\Facades\Hash;

class InsuranceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::beginTransaction();

        $data = loadJsonData("demo");
        $User = User::first();
        if (!$User) {
            $User = User::create([
                'name' => 'Admin',
                'prenom' => 'Seeder',
                'email' => 'admin@medkey.com',
                'password' => Hash::make('MotDePasse'),
                'email_verified_at' => now()->toDateTimeString(),
            ]);
            if (method_exists($User, 'assignRole')) {
                $User->assignRole('Admin');
            }
        }

        try {

            $insurances = collect($data->insurances)->map(
                function ($d) use ($User) {
                    $d->users_id = $User->id;
                    $d->uuid = Str::uuid();
                    $d->created_at = Carbon::now();
                    $d->updated_at = Carbon::now();
                    return (array)$d;
                }
            );

            Insurance::insert($insurances->toArray());

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
        }
    }
}
