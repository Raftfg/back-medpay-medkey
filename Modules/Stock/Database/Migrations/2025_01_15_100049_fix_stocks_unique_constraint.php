<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Correction de la contrainte unique sur stocks
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('stocks', 'hospital_id')) {
            return;
        }

        Schema::table('stocks', function (Blueprint $table) {
            // Supprimer la contrainte unique sur 'name'
            try {
                $table->dropUnique(['name']);
            } catch (\Exception $e) {
                $indexes = DB::select("SHOW INDEX FROM stocks WHERE Column_name = 'name' AND Non_unique = 0");
                foreach ($indexes as $index) {
                    try {
                        $table->dropIndex($index->Key_name);
                    } catch (\Exception $ex) {
                        // Ignorer
                    }
                }
            }
        });

        Schema::table('stocks', function (Blueprint $table) {
            // Ajouter une contrainte unique composite sur (hospital_id, name)
            $table->unique(['hospital_id', 'name'], 'stocks_hospital_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropUnique('stocks_hospital_name_unique');
            $table->unique('name');
        });
    }
};
