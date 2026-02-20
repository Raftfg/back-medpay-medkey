<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Administration\Entities\Hospital;
use Illuminate\Support\Facades\DB;

/**
 * Migration : Ajout de hospital_id à la table services
 * 
 * Cette migration ajoute la colonne hospital_id pour l'isolation multi-tenant.
 * Les services existants seront associés au premier hôpital actif.
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
        Schema::table('services', function (Blueprint $table) {
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

        // Assigner les services existants au premier hôpital actif
        $firstHospital = Hospital::active()->first();
        if ($firstHospital) {
            DB::table('services')->whereNull('hospital_id')->update(['hospital_id' => $firstHospital->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'hospital_id')) {
                if (Schema::hasTable('hospitals')) {
                    $table->dropForeign(['hospital_id']);
                }
                $table->dropIndex(['hospital_id']);
                $table->dropColumn('hospital_id');
            }
        });
    }
};
