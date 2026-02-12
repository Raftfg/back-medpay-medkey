<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Correction de la contrainte unique sur conditioning_units
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('conditioning_units', 'hospital_id')) {
            return;
        }

        Schema::table('conditioning_units', function (Blueprint $table) {
            try {
                $table->dropUnique(['name']);
            } catch (\Exception $e) {
                $indexes = DB::select("SHOW INDEX FROM conditioning_units WHERE Column_name = 'name' AND Non_unique = 0");
                foreach ($indexes as $index) {
                    try {
                        $table->dropIndex($index->Key_name);
                    } catch (\Exception $ex) {
                        // Ignorer
                    }
                }
            }
        });

        Schema::table('conditioning_units', function (Blueprint $table) {
            $table->unique(['hospital_id', 'name'], 'conditioning_units_hospital_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('conditioning_units', function (Blueprint $table) {
            $table->dropUnique('conditioning_units_hospital_name_unique');
            $table->unique('name');
        });
    }
};
