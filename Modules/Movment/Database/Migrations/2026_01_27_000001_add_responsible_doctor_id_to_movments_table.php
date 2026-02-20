<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration : Ajout de responsible_doctor_id à la table movments
 * 
 * Cette migration ajoute la colonne responsible_doctor_id pour associer
 * un médecin responsable à chaque mouvement d'admission.
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
            if (!Schema::hasColumn('movments', 'responsible_doctor_id')) {
                $table->unsignedBigInteger('responsible_doctor_id')
                    ->nullable()
                    ->after('admission_type')
                    ->comment('ID du médecin responsable de l\'admission');
            }
        });

        if (Schema::hasTable('movments') && Schema::hasColumn('movments', 'responsible_doctor_id') && Schema::hasTable('users')) {
            Schema::table('movments', function (Blueprint $table) {
                $table->foreign('responsible_doctor_id')
                    ->references('id')
                    ->on('users')
                    ->onUpdate('cascade')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('movments', function (Blueprint $table) {
            if (Schema::hasColumn('movments', 'responsible_doctor_id')) {
                // Supprimer la foreign key si elle existe
                try {
                    $table->dropForeign(['responsible_doctor_id']);
                } catch (\Exception $e) {
                    // Ignorer si la foreign key n'existe pas
                }
                
                $table->dropColumn('responsible_doctor_id');
            }
        });
    }
};
