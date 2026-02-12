<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Correction de la contrainte unique sur categories
 * 
 * Remplace la contrainte unique sur 'name' seul par une contrainte unique composite
 * sur (hospital_id, name) pour permettre le même nom dans différents hôpitaux.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'hospital_id')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            try {
                $table->dropUnique(['name']);
            } catch (\Exception $e) {
                $indexes = DB::select("SHOW INDEX FROM categories WHERE Column_name = 'name' AND Non_unique = 0");
                foreach ($indexes as $index) {
                    try {
                        $table->dropIndex($index->Key_name);
                    } catch (\Exception $ex) {
                        // Ignorer
                    }
                }
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['hospital_id', 'name'], 'categories_hospital_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_hospital_name_unique');
            $table->unique('name');
        });
    }
};
