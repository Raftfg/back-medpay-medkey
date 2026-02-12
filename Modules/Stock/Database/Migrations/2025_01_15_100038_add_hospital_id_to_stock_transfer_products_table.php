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
        Schema::table('stock_transfer_products', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_transfer_products', 'hospital_id')) {
                $table->unsignedBigInteger('hospital_id')->nullable()->after('id');
                $table->foreign('hospital_id')
                      ->references('id')
                      ->on('hospitals')
                      ->onDelete('restrict');
            }
        });

        // Assigner hospital_id depuis la relation stock_transfer
        $defaultHospital = Hospital::active()->first();
        if ($defaultHospital) {
            DB::statement("
                UPDATE stock_transfer_products stp
                INNER JOIN stock_transfers st ON stp.stock_transfer_id = st.id
                SET stp.hospital_id = COALESCE(st.hospital_id, {$defaultHospital->id})
                WHERE stp.hospital_id IS NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::table('stock_transfer_products', function (Blueprint $table) {
            if (Schema::hasColumn('stock_transfer_products', 'hospital_id')) {
                $table->dropForeign(['hospital_id']);
                $table->dropColumn('hospital_id');
            }
        });
    }
};
