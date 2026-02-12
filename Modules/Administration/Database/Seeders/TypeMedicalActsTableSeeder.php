<?php

namespace Modules\Administration\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;


use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

use Modules\Administration\Entities\TypeMedicalActs;

class TypeMedicalActsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $data = loadJsonData("demo");
        try {
            $type_medical_acts = collect($data->type_medical_acts)->map(
                function ($d) {
                    $d->created_at = Carbon::now();
                    $d->updated_at = Carbon::now();
                    return (array)$d;
                }
            );

            TypeMedicalActs::insert($type_medical_acts->toArray());

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
        }
    }
}
