<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration pour la table hospitals (Tenants)
 * 
 * Cette table stocke les informations de chaque hôpital (tenant) de la plateforme multi-tenant.
 * Chaque hôpital a son propre domaine et ses propres données isolées.
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
    public function up()
    {
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('name'); // Nom de l'hôpital
            $table->string('domain')->unique(); // Domaine unique (ex: hopital1.ma-plateforme.com)
            $table->string('slug')->unique()->nullable(); // Slug pour URL (ex: hopital1)
            
            // Statut et configuration
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            
            // Informations complémentaires (optionnelles)
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable(); // Chemin vers le logo
            $table->text('description')->nullable();
            
            // Métadonnées
            $table->uuid('uuid')->unique()->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // Utilisateur qui a créé l'hôpital
            $table->boolean('is_synced')->default(0);
            $table->timestamp('deleted_at')->nullable();
            
            // Foreign key vers users (créateur)
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('set null');
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('domain');
            $table->index('status');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hospitals');
    }
};
