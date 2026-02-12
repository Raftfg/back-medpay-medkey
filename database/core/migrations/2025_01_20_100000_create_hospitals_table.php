<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration CORE : Table hospitals
 * 
 * Cette table stocke les informations de chaque hôpital (tenant) dans la base CORE.
 * Chaque hôpital a son propre domaine et sa propre base de données MySQL.
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
        Schema::connection('core')->create('hospitals', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('name'); // Nom de l'hôpital
            $table->string('domain')->unique(); // Domaine unique (ex: hopital1.ma-plateforme.com)
            $table->string('slug')->unique()->nullable(); // Slug pour URL (ex: hopital1)
            
            // Configuration de la base de données tenant
            $table->string('database_name'); // Nom de la base de données (ex: medkey_hospital_1)
            $table->string('database_host')->default('127.0.0.1'); // Host de la base (peut être différent par hôpital)
            $table->string('database_port')->default('3306'); // Port de la base
            $table->string('database_username')->nullable(); // Username spécifique (optionnel)
            $table->string('database_password')->nullable(); // Password spécifique (optionnel, chiffré)
            
            // Statut et configuration
            $table->enum('status', ['active', 'inactive', 'suspended', 'provisioning'])->default('provisioning');
            
            // Informations complémentaires (optionnelles)
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable(); // Chemin vers le logo
            $table->text('description')->nullable();
            
            // Métadonnées
            $table->uuid('uuid')->unique()->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // ID de l'admin système qui a créé l'hôpital
            $table->boolean('is_synced')->default(0);
            $table->timestamp('provisioned_at')->nullable(); // Date de provisioning
            $table->timestamp('deleted_at')->nullable();
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('domain');
            $table->index('status');
            $table->index('slug');
            $table->index('database_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('core')->dropIfExists('hospitals');
    }
};
