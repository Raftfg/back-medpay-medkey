<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Ajout de hospital_id à la table cash_registers
 * 
 * Cette migration ajoute la colonne hospital_id pour l'isolation multi-tenant.
 * Les données existantes seront associées au premier hôpital actif par défaut.
 * 
 * @package Modules\Cash\Database\Migrations
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
        Schema::table('cash_registers', function (Blueprint $table) {
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

        // Assigner les caisses existantes au premier hôpital actif
        $firstHospital = DB::table('hospitals')
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        if ($firstHospital) {
            DB::table('cash_registers')
                ->whereNull('hospital_id')
                ->update(['hospital_id' => $firstHospital->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('cash_registers', function (Blueprint $table) {
            // Supprimer la foreign key
            $table->dropForeign(['hospital_id']);
            
            // Supprimer l'index
            $table->dropIndex(['hospital_id']);
            
            // Supprimer la colonne
            $table->dropColumn('hospital_id');
        });
    }
};
