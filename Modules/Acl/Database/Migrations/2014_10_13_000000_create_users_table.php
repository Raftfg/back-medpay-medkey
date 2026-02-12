<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('prenom')->nullable();
            $table->string('adresse')->nullable();
            $table->string('telephone')->nullable();
            $table->string('sexe')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('tel_mobile_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
//            $table->string('adresse_code_civic')->nullable()->comment("Numéro de porte");
//            $table->string('adresse_rue')->nullable()->comment("Nom de la rue");
//            $table->string('adresse_apt')->nullable()->comment("Numéro appartement");
//            $table->string('adresse_code_postal')->nullable();
//            $table->string('tel')->nullable()->comment("Tél fixe");
            $table->string('tel_mobile')->unique()->nullable()->comment("Cellulaire");
            $table->string('tel_mobile_code')->nullable()->comment("Code SMS");
            
            $table->timestamps();
            
            $table->softDeletes();
            
            $table->uuid('uuid')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
