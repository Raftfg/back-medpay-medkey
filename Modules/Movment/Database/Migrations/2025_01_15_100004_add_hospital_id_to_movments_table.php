<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Ajout de hospital_id à la table movments
 * 
 * Cette migration ajoute la colonne hospital_id pour l'isolation multi-tenant.
 * Les données existantes seront associées au premier hôpital actif par défaut.
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

        // Assigner les mouvements existants au premier hôpital actif
        // On récupère le hospital_id du patient associé
        $firstHospital = DB::table('hospitals')
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        if ($firstHospital) {
            // Mettre à jour les mouvements en fonction du hospital_id du patient
            DB::statement('
                UPDATE movments m
                INNER JOIN patients p ON m.patients_id = p.id
                SET m.hospital_id = COALESCE(p.hospital_id, ?)
                WHERE m.hospital_id IS NULL
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
        Schema::table('movments', function (Blueprint $table) {
            // Supprimer la foreign key
            $table->dropForeign(['hospital_id']);
            
            // Supprimer l'index
            $table->dropIndex(['hospital_id']);
            
            // Supprimer la colonne
            $table->dropColumn('hospital_id');
        });
    }
};
