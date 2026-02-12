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
        Schema::table('stock_products', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_products', 'hospital_id')) {
                $table->unsignedBigInteger('hospital_id')->nullable()->after('id');
                $table->foreign('hospital_id')
                      ->references('id')
                      ->on('hospitals')
                      ->onDelete('restrict');
            }
        });

        // Assigner hospital_id depuis la relation stock
        $defaultHospital = Hospital::active()->first();
        if ($defaultHospital) {
            // Mettre à jour via la relation stock
            DB::statement("
                UPDATE stock_products sp
                INNER JOIN stocks s ON sp.stock_id = s.id
                SET sp.hospital_id = COALESCE(s.hospital_id, {$defaultHospital->id})
                WHERE sp.hospital_id IS NULL
            ");
        }
    }

    public function down(): void
    {
        Schema::table('stock_products', function (Blueprint $table) {
            if (Schema::hasColumn('stock_products', 'hospital_id')) {
                $table->dropForeign(['hospital_id']);
                $table->dropColumn('hospital_id');
            }
        });
    }
};
