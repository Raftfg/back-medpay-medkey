<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Migration CORE : Table system_admins
 * 
 * Cette table stocke les administrateurs système qui gèrent la plateforme multi-tenant.
 * Ces administrateurs ont accès à la base CORE et peuvent créer/gérer les hôpitaux.
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
        Schema::connection('core')->create('system_admins', function (Blueprint $table) {
            $table->id();
            
            // Informations de base
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // Permissions système
            $table->json('permissions')->nullable(); // Permissions spécifiques (ex: ['create_tenant', 'manage_tenants'])
            $table->enum('role', ['super_admin', 'admin', 'support'])->default('support');
            
            // Statut
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            
            // Métadonnées
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            // Index pour améliorer les performances
            $table->index('email');
            $table->index('role');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('core')->dropIfExists('system_admins');
    }
};
