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
            $table->timestamps();
        });

        // En provisioning multi-tenant, l'ordre des migrations peut varier.
        // On ajoute les contraintes uniquement si les tables cibles existent.
        Schema::table('allocate_cash_registers', function (Blueprint $table) {
            if (Schema::hasTable('cash_registers')) {
                $table->foreign('cash_registers_id')
                    ->references('id')
                    ->on('cash_registers')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            }

            if (Schema::hasTable('cashiers')) {
                $table->foreign('cashiers_id')
                    ->references('id')
                    ->on('cashiers')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            }
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
