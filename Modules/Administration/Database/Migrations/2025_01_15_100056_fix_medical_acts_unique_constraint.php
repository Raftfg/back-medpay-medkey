<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Correction de la contrainte unique sur medical_acts
 * 
 * Remplace la contrainte unique sur 'code' par une contrainte unique composite
 * sur (hospital_id, code) pour permettre le même code dans différents hôpitaux.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('medical_acts', 'hospital_id')) {
            return;
        }

        Schema::table('medical_acts', function (Blueprint $table) {
            // Supprimer la contrainte unique sur 'code'
            try {
                $table->dropUnique(['code']);
            } catch (\Exception $e) {
                $indexes = DB::select("SHOW INDEX FROM medical_acts WHERE Column_name = 'code' AND Non_unique = 0");
                foreach ($indexes as $index) {
                    try {
                        $table->dropIndex($index->Key_name);
                    } catch (\Exception $ex) {
                        // Ignorer
                    }
                }
            }
        });

        Schema::table('medical_acts', function (Blueprint $table) {
            // Ajouter une contrainte unique composite sur (hospital_id, code)
            $table->unique(['hospital_id', 'code'], 'medical_acts_hospital_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('medical_acts', function (Blueprint $table) {
            $table->dropUnique('medical_acts_hospital_code_unique');
            $table->unique('code');
        });
    }
};
