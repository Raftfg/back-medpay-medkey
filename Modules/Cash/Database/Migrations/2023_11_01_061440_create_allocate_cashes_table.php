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
        Schema::create('allocate_cashes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cashier_id');
            $table->date('selected_date');
            $table->time('opening_time');
            $table->time('closing_time');
            $table->boolean('is_choose')->default(0); // concerne juste le choix de la caisse selectionné
            $table->unsignedBigInteger('cash_registers_id');
            $table->boolean('statut'); // Permet de savoir si le caissier est actif ou inactif
            $table->uuid('uuid')->unique()->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->boolean('is_synced')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('allocate_cashes')) {
            Schema::table('allocate_cashes', function (Blueprint $table) {
                if (Schema::hasTable('cash_registers')) {
                    $table->foreign('cash_registers_id')->references('id')->on('cash_registers')->onDelete('cascade')->onUpdate('cascade');
                }

                if (Schema::hasTable('users')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
                    $table->foreign('cashier_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('allocate_cashes');
    }
};
