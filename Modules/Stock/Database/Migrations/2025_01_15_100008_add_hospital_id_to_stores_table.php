<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Ajout de hospital_id à la table stores
 * 
 * Cette migration ajoute la colonne hospital_id pour l'isolation multi-tenant.
 * Les données existantes seront associées au hospital_id de l'utilisateur associé.
 * 
 * @package Modules\Stock\Database\Migrations
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
        Schema::table('stores', function (Blueprint $table) {
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

        // Assigner les magasins existants en fonction du hospital_id de l'utilisateur
        $firstHospital = DB::table('hospitals')
            ->where('status', 'active')
            ->orderBy('id')
            ->first();

        if ($firstHospital) {
            // Mettre à jour les magasins en fonction du hospital_id de l'utilisateur
            DB::statement('
                UPDATE stores s
                INNER JOIN users u ON s.user_id = u.id
                SET s.hospital_id = COALESCE(u.hospital_id, ?)
                WHERE s.hospital_id IS NULL
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
        Schema::table('stores', function (Blueprint $table) {
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
