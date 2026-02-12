<?php

namespace Modules\Administration\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Modules\Administration\Entities\Departement;
use Modules\Administration\Entities\pays;

class DepartementTableSeeder extends Seeder
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
        $pay = pays::first();

        try {

            $departements = collect($data->departements)->map(
                function ($d) use ($pay) {
                    $d->pays_id = $pay->id;
                    $d->created_at = Carbon::now();
                    $d->updated_at = Carbon::now();
                    return (array)$d;
                }
            );

            Departement::insert($departements->toArray());

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
        }
    }
}
