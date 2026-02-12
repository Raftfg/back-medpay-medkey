<?php

namespace Modules\Administration\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;


use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

use Modules\Administration\Entities\Department;

class DepartmentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        try {
            $data = loadJsonData("demo");
            $departments = collect($data->departments)->map(function ($p) {
                $p->created_at = Carbon::now();
                 $p->uuid = Str::uuid();
                $p->updated_at = Carbon::now();
                return (array)$p;
            });

            Department::insert($departments->toArray());

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th);
        }
    }
}
