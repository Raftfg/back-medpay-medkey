<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration : Ajout de admission_type à la table movments
 * 
 * Cette migration ajoute la colonne admission_type pour distinguer
 * les admissions programmées des admissions en urgence.
 * 
 * @package Modules\Movment\Database\Migrations
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
        Schema::table('movments', function (Blueprint $table) {
            if (!Schema::hasColumn('movments', 'admission_type')) {
                $table->string('admission_type')
                    ->default('programmée')
                    ->after('incoming_reason')
                    ->comment('Type d\'admission: programmée ou urgence');
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
        Schema::table('movments', function (Blueprint $table) {
            if (Schema::hasColumn('movments', 'admission_type')) {
                $table->dropColumn('admission_type');
            }
        });
    }
};
