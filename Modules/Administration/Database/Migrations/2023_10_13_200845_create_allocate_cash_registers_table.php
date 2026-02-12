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
        Schema::create('allocate_cash_registers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cash_registers_id');
            $table->unsignedBigInteger('cashiers_id');

            // Définition des clés étrangères avec l'option "on cascade"
            $table->foreign('cash_registers_id')
                ->references('id')
                ->on('cash_registers')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('cashiers_id')
                ->references('id')
                ->on('cashiers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('allocate_cash_registers');
    }
};
