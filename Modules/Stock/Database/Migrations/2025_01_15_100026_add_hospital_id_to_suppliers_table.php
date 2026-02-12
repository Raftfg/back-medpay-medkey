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
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'hospital_id')) {
                $table->unsignedBigInteger('hospital_id')->nullable()->after('id');
                $table->foreign('hospital_id')
                      ->references('id')
                      ->on('hospitals')
                      ->onDelete('restrict');
            }
        });

        $defaultHospital = Hospital::active()->first();
        if ($defaultHospital) {
            DB::table('suppliers')->whereNull('hospital_id')->update(['hospital_id' => $defaultHospital->id]);
        }
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (Schema::hasColumn('suppliers', 'hospital_id')) {
                $table->dropForeign(['hospital_id']);
                $table->dropColumn('hospital_id');
            }
        });
    }
};
