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
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable(); // UUID unique
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_synced')->default(0); // Défaut à 0
            $table->unsignedBigInteger('movement_id');
            $table->integer('payment_method_id');
            $table->unsignedBigInteger('cash_register_id');
            $table->integer('number');
            $table->string('lastname');
            $table->string('firstname');
            $table->integer('montant');
            $table->boolean('status');
            $table->timestamp('deleted_at')->nullable();

            $table->timestamps();

            $table->foreign('movement_id')->references('id')->on('movments')->restrictOnDelete();
            $table->foreign('cash_register_id')->references('id')->on('cash_registers')->restrictOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->restrictOnDelete(); // Exemple de clé étrangère vers la table des utilisateurs (modifiez selon vos besoins).
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    
    public function down()
    {
        Schema::dropIfExists('operations');
    }

};
