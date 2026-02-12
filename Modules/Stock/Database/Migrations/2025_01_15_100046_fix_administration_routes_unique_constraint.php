<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Correction de la contrainte unique sur administration_routes
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('administration_routes', 'hospital_id')) {
            return;
        }

        Schema::table('administration_routes', function (Blueprint $table) {
            try {
                $table->dropUnique(['name']);
            } catch (\Exception $e) {
                $indexes = DB::select("SHOW INDEX FROM administration_routes WHERE Column_name = 'name' AND Non_unique = 0");
                foreach ($indexes as $index) {
                    try {
                        $table->dropIndex($index->Key_name);
                    } catch (\Exception $ex) {
                        // Ignorer
                    }
                }
            }
        });

        Schema::table('administration_routes', function (Blueprint $table) {
            $table->unique(['hospital_id', 'name'], 'administration_routes_hospital_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('administration_routes', function (Blueprint $table) {
            $table->dropUnique('administration_routes_hospital_name_unique');
            $table->unique('name');
        });
    }
};
