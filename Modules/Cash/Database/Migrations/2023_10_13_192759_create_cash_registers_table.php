<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable(); // UUID unique
            $table->unsignedBigInteger('user_id');
            $table->string('designation');
            $table->string('description');
            $table->string('type')->nullable();
            $table->decimal('total_partial')->default(0)->nullable();
            $table->decimal('solde')->default(0)->nullable();
            $table->decimal('credits')->default(0)->nullable();
            $table->decimal('total_espece')->default(0)->nullable();
            $table->decimal('totalMtnMomo')->default(0)->nullable();
            $table->decimal('totalMoovMomo')->default(0)->nullable();
            $table->decimal('totalCeltis')->default(0)->nullable();
            $table->decimal('totalCarteBancaire')->default(0)->nullable();
            $table->decimal('totalCarteCredit')->default(0)->nullable();
            $table->decimal('totalTresorPay')->default(0)->nullable();
            $table->boolean('statut')->default(1);
            $table->timestamp('deleted_at')->nullable();
            $table->boolean('is_synced')->default(0); // Défaut à 0
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade'); // Exemple de clé étrangère vers la table des utilisateurs (modifiez selon vos besoins).
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_registers');
    }
};
