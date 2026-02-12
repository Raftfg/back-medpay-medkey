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
        $pt = config('notifier.prefixe_table', '');
        $tableName = $pt . 'notifier_trackings';
        
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (!Schema::hasColumn($tableName, 'hospital_id')) {
                $table->unsignedBigInteger('hospital_id')->nullable()->after('id');
                $table->foreign('hospital_id')
                      ->references('id')
                      ->on('hospitals')
                      ->onDelete('restrict');
            }
        });

        $defaultHospital = Hospital::active()->first();
        if ($defaultHospital) {
            DB::table($tableName)->whereNull('hospital_id')->update(['hospital_id' => $defaultHospital->id]);
        }
    }

    public function down(): void
    {
        $pt = config('notifier.prefixe_table', '');
        $tableName = $pt . 'notifier_trackings';
        
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            if (Schema::hasColumn($tableName, 'hospital_id')) {
                $table->dropForeign(['hospital_id']);
                $table->dropColumn('hospital_id');
            }
        });
    }
};
