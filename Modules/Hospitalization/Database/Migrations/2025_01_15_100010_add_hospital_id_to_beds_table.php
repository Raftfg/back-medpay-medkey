<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Ajout de hospital_id à la table beds
 * 
 * Cette migration ajoute la colonne hospital_id pour l'isolation multi-tenant.
 * Les données existantes seront associées au hospital_id de la chambre associée.
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
        Schema::table('beds', function (Blueprint $table) {
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

        // Assigner les lits existants en fonction du hospital_id de la chambre
        $firstHospital = DB::table('hospitals')
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        if ($firstHospital) {
            // Mettre à jour les lits en fonction du hospital_id de la chambre
            DB::statement('
                UPDATE beds b
                INNER JOIN rooms r ON b.room_id = r.id
                SET b.hospital_id = COALESCE(r.hospital_id, ?)
                WHERE b.hospital_id IS NULL
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
        Schema::table('beds', function (Blueprint $table) {
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
