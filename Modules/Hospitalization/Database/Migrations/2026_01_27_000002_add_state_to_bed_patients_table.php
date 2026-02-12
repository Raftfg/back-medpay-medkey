<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration : Ajout de state à la table bed_patients
 * 
 * Cette migration ajoute la colonne state pour indiquer l'état
 * de l'occupation du lit (busy, free, cleaning, etc.)
 * 
 * @package Modules\Hospitalization\Database\Migrations
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('bed_patients', function (Blueprint $table) {
            if (!Schema::hasColumn('bed_patients', 'state')) {
                $table->string('state')
                    ->default('busy')
                    ->after('end_occupation_date')
                    ->comment('État de l\'occupation: busy, free, cleaning');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('bed_patients', function (Blueprint $table) {
            if (Schema::hasColumn('bed_patients', 'state')) {
                $table->dropColumn('state');
            }
        });
    }
};
