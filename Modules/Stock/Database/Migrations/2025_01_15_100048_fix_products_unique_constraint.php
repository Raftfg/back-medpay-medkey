<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Correction de la contrainte unique sur products
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'hospital_id')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            // Supprimer la contrainte unique sur 'code'
            try {
                $table->dropUnique(['code']);
            } catch (\Exception $e) {
                $indexes = DB::select("SHOW INDEX FROM products WHERE Column_name = 'code' AND Non_unique = 0");
                foreach ($indexes as $index) {
                    try {
                        $table->dropIndex($index->Key_name);
                    } catch (\Exception $ex) {
                        // Ignorer
                    }
                }
            }
        });

        Schema::table('products', function (Blueprint $table) {
            // Ajouter une contrainte unique composite sur (hospital_id, code)
            $table->unique(['hospital_id', 'code'], 'products_hospital_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_hospital_code_unique');
            $table->unique('code');
        });
    }
};
