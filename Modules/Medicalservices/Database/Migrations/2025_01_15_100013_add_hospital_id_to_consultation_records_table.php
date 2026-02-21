<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Administration\Entities\Hospital;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('consultation_records', function (Blueprint $table) {
            if (!Schema::hasColumn('consultation_records', 'hospital_id')) {
                $table->unsignedBigInteger('hospital_id')->nullable()->after('id');
                if (Schema::hasTable('hospitals')) {
                    $table->foreign('hospital_id')
                          ->references('id')
                          ->on('hospitals')
                          ->onDelete('restrict');
                }
            }
        });

        // Assigner un hospital_id par défaut aux enregistrements existants
        $defaultHospital = Hospital::active()->first();
        if ($defaultHospital) {
            DB::table('consultation_records')->whereNull('hospital_id')->update(['hospital_id' => $defaultHospital->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('consultation_records', function (Blueprint $table) {
            if (Schema::hasColumn('consultation_records', 'hospital_id')) {
                if (Schema::hasTable('hospitals')) {
                    $table->dropForeign(['hospital_id']);
                }
                $table->dropColumn('hospital_id');
            }
        });
    }
};
