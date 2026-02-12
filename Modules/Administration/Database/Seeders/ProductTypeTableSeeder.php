<?php

namespace Modules\Administration\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;


use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

use Modules\Administration\Entities\ProductType;
use Modules\Acl\Entities\User;

class ProductTypeTableSeeder extends Seeder
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

        try {

            $typesproduits = collect($data->typesproduits)->map(
                function ($d) use ($User) {
                    $d->users_id = $User->id;
                     $d->uuid = Str::uuid();
                    $d->created_at = Carbon::now();
                    $d->updated_at = Carbon::now();
                    return (array)$d;
                }
            );

            ProductType::insert($typesproduits->toArray());

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
        }
    }
}
