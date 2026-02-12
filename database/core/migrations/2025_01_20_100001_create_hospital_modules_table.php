<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration CORE : Table hospital_modules
 * 
 * Cette table stocke les modules activés pour chaque hôpital.
 * Permet de gérer l'activation/désactivation de modules par tenant.
 * 
 * @package Database\Core\Migrations
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('core')->create('hospital_modules', function (Blueprint $table) {
            $table->id();
            
            // Référence vers l'hôpital
            $table->unsignedBigInteger('hospital_id');
            
            // Informations du module
            $table->string('module_name'); // Nom du module (ex: 'Patient', 'Payment', 'Stock')
            $table->boolean('is_enabled')->default(true); // Module activé ou non
            $table->json('config')->nullable(); // Configuration spécifique du module pour cet hôpital
            
            // Métadonnées
            $table->timestamp('enabled_at')->nullable(); // Date d'activation
            $table->timestamp('disabled_at')->nullable(); // Date de désactivation
            $table->unsignedBigInteger('enabled_by')->nullable(); // Admin qui a activé
            $table->text('notes')->nullable(); // Notes sur l'activation/désactivation
            
            $table->timestamps();
            
            // Foreign key vers hospitals
            $table->foreign('hospital_id')
                ->references('id')
                ->on('hospitals')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            // Index unique pour éviter les doublons
            $table->unique(['hospital_id', 'module_name']);
            
            // Index pour améliorer les performances
            $table->index('hospital_id');
            $table->index('module_name');
            $table->index('is_enabled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('core')->dropIfExists('hospital_modules');
    }
};
