<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration : Ajout de hospital_id à la table bed_patients
 * 
 * Cette migration ajoute la colonne hospital_id pour l'isolation multi-tenant.
 * Les données existantes seront associées au hospital_id du patient associé.
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
            // Ajouter la colonne hospital_id (nullable pour permettre la migration progressive)
            $table->unsignedBigInteger('hospital_id')->nullable()->after('id');
            
            // Ajouter l'index pour améliorer les performances
            $table->index('hospital_id');
            
            // Ajouter la foreign key vers hospitals
            if (Schema::hasTable('hospitals')) {
                $table->foreign('hospital_id')
                    ->references('id')
                    ->on('hospitals')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');
            }
        });

        // Assigner les relations lit-patient existantes en fonction du hospital_id du patient
        $firstHospital = DB::table('hospitals')
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        if ($firstHospital) {
            // Mettre à jour les relations en fonction du hospital_id du patient
            DB::statement('
                UPDATE bed_patients bp
                INNER JOIN patients p ON bp.patient_id = p.id
                SET bp.hospital_id = COALESCE(p.hospital_id, ?)
                WHERE bp.hospital_id IS NULL
            ', [$firstHospital->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('bed_patients', function (Blueprint $table) {
            // Supprimer la foreign key
            if (Schema::hasTable('hospitals')) {
                $table->dropForeign(['hospital_id']);
            }
            
            // Supprimer l'index
            $table->dropIndex(['hospital_id']);
            
            // Supprimer la colonne
            $table->dropColumn('hospital_id');
        });
    }
};
