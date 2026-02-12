<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Administration\Entities\Hospital;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Ajout de hospital_id à la table medical_acts
 * 
 * Cette migration ajoute la colonne hospital_id pour l'isolation multi-tenant.
 * Les actes médicaux existants seront associés au hospital_id du service associé.
 * 
 * @package Modules\Administration\Database\Migrations
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
        Schema::table('medical_acts', function (Blueprint $table) {
            // Ajouter la colonne hospital_id (nullable pour permettre la migration progressive)
            $table->unsignedBigInteger('hospital_id')->nullable()->after('id');
            
            // Ajouter l'index pour améliorer les performances
            $table->index('hospital_id');
            
            // Ajouter la foreign key vers hospitals
            $table->foreign('hospital_id')
                ->references('id')
                ->on('hospitals')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        // Assigner les actes médicaux existants en fonction du hospital_id du service
        $firstHospital = Hospital::active()->first();
        if ($firstHospital) {
            // Mettre à jour les actes médicaux en fonction du hospital_id du service
            DB::statement('
                UPDATE medical_acts ma
                INNER JOIN services s ON ma.services_id = s.id
                SET ma.hospital_id = COALESCE(s.hospital_id, ?)
                WHERE ma.hospital_id IS NULL
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
        Schema::table('medical_acts', function (Blueprint $table) {
            if (Schema::hasColumn('medical_acts', 'hospital_id')) {
                $table->dropForeign(['hospital_id']);
                $table->dropIndex(['hospital_id']);
                $table->dropColumn('hospital_id');
            }
        });
    }
};
