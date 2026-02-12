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
        //
        Schema::create('signataires', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('type_document');
            $table->string('titre');
            $table->enum('statut', ['Actif', 'Inactif']);
            $table->binary('signature')->nullable(); // Utilisation de BLOB pour stocker le contenu du fichier
            $table->timestamps();
            $table->softDeletes();
    
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('signataires');
    }
};
