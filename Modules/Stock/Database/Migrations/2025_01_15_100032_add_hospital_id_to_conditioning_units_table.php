<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Administration\Entities\Hospital;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conditioning_units', function (Blueprint $table) {
            if (!Schema::hasColumn('conditioning_units', 'hospital_id')) {
                $table->unsignedBigInteger('hospital_id')->nullable()->after('id');
                $table->foreign('hospital_id')
                      ->references('id')
                      ->on('hospitals')
                      ->onDelete('restrict');
            }
        });

        $defaultHospital = Hospital::active()->first();
        if ($defaultHospital) {
            DB::table('conditioning_units')->whereNull('hospital_id')->update(['hospital_id' => $defaultHospital->id]);
        }
    }

    public function down(): void
    {
        Schema::table('conditioning_units', function (Blueprint $table) {
            if (Schema::hasColumn('conditioning_units', 'hospital_id')) {
                $table->dropForeign(['hospital_id']);
                $table->dropColumn('hospital_id');
            }
        });
    }
};
