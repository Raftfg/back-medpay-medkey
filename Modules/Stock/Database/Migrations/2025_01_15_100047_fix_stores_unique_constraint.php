<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Correction de la contrainte unique sur stores
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('stores', 'hospital_id')) {
            return;
        }

        Schema::table('stores', function (Blueprint $table) {
            // Supprimer les contraintes uniques sur 'code' et 'name'
            try {
                $table->dropUnique(['code']);
            } catch (\Exception $e) {
                $indexes = DB::select("SHOW INDEX FROM stores WHERE Column_name = 'code' AND Non_unique = 0");
                foreach ($indexes as $index) {
                    try {
                        $table->dropIndex($index->Key_name);
                    } catch (\Exception $ex) {
                        // Ignorer
                    }
                }
            }
            
            try {
                $table->dropUnique(['name']);
            } catch (\Exception $e) {
                $indexes = DB::select("SHOW INDEX FROM stores WHERE Column_name = 'name' AND Non_unique = 0");
                foreach ($indexes as $index) {
                    try {
                        $table->dropIndex($index->Key_name);
                    } catch (\Exception $ex) {
                        // Ignorer
                    }
                }
            }
        });

        Schema::table('stores', function (Blueprint $table) {
            // Ajouter des contraintes uniques composites
            $table->unique(['hospital_id', 'code'], 'stores_hospital_code_unique');
            $table->unique(['hospital_id', 'name'], 'stores_hospital_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropUnique('stores_hospital_code_unique');
            $table->dropUnique('stores_hospital_name_unique');
            $table->unique('code');
            $table->unique('name');
        });
    }
};
