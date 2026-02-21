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
        Schema::table('supply_products', function (Blueprint $table) {
            if (!Schema::hasColumn('supply_products', 'hospital_id')) {
                $table->unsignedBigInteger('hospital_id')->nullable()->after('id');
                if (Schema::hasTable('hospitals')) {
                    $table->foreign('hospital_id')
                          ->references('id')
                          ->on('hospitals')
                          ->onDelete('restrict');
                }
            }
        });

        // Assigner hospital_id depuis la relation supply
        $defaultHospital = Hospital::active()->first();
        if ($defaultHospital) {
            DB::statement("
                UPDATE supply_products sp
                INNER JOIN supplies s ON sp.supply_id = s.id
                SET sp.hospital_id = COALESCE(s.hospital_id, {$defaultHospital->id})
                WHERE sp.hospital_id IS NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::table('supply_products', function (Blueprint $table) {
            if (Schema::hasColumn('supply_products', 'hospital_id')) {
                if (Schema::hasTable('hospitals')) {
                    $table->dropForeign(['hospital_id']);
                }
                $table->dropColumn('hospital_id');
            }
        });
    }
};
