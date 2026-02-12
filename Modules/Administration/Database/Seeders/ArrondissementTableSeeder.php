<?php

namespace Modules\Administration\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;


use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Modules\Administration\Entities\Arrondissement;
use Modules\Administration\Entities\Commune;


class ArrondissementTableSeeder extends Seeder
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
        $Commune = Commune::first();

        try {

            $arrondissments = collect($data->arrondissments)->map(
                function ($d) use ($Commune) {
                    $d->communes_id = $Commune->id;
                    $d->created_at = Carbon::now();
                    $d->updated_at = Carbon::now();
                    return (array)$d;
                }
            );

            Arrondissement::insert($arrondissments->toArray());

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
        }
    }
}
