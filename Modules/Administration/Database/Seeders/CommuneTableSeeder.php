<?php

namespace Modules\Administration\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;



use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Modules\Administration\Entities\Departement;
use Modules\Administration\Entities\Commune;

class CommuneTableSeeder extends Seeder
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
        $Departement = Departement::first();

        try {

            $communes = collect($data->communes)->map(
                function ($d) use ($Departement) {
                    $d->departements_id = $Departement->id;
                    $d->created_at = Carbon::now();
                    $d->updated_at = Carbon::now();
                    return (array)$d;
                }
            );

            Commune::insert($communes->toArray());

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
        }
    }
}
