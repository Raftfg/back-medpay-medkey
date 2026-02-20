<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Ajout de hospital_id à la table users
 * 
 * Cette migration ajoute la colonne hospital_id pour l'isolation multi-tenant.
 * Les données existantes seront associées au premier hôpital actif par défaut.
 * 
 * @package Modules\Acl\Database\Migrations
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
        Schema::table('users', function (Blueprint $table) {
            // Ajouter la colonne hospital_id (nullable pour permettre la migration progressive)
            $table->unsignedBigInteger('hospital_id')->nullable()->after('id');
            
            // Ajouter l'index pour améliorer les performances
            $table->index('hospital_id');
            
            // En mode multi-tenant database-per-tenant, la table hospitals n'existe
            // pas forcément dans la base tenant (elle est dans CORE).
            // On n'ajoute donc la FK que si la table cible existe.
            if (Schema::hasTable('hospitals')) {
                $table->foreign('hospital_id')
                    ->references('id')
                    ->on('hospitals')
                    ->onUpdate('cascade')
                    ->onDelete('restrict'); // Empêche la suppression d'un hôpital s'il a des utilisateurs
            }
        });

        // Assigner les utilisateurs existants au premier hôpital actif
        // uniquement si la table hospitals existe sur la connexion courante.
        if (Schema::hasTable('hospitals')) {
            $firstHospital = DB::table('hospitals')
                ->where('status', 'active')
                ->orderBy('id')
                ->first();

            if ($firstHospital) {
                DB::table('users')
                    ->whereNull('hospital_id')
                    ->update(['hospital_id' => $firstHospital->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Supprimer la foreign key si elle existe.
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
