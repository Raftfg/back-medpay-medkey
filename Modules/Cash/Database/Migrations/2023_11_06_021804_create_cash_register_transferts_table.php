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
        Schema::create('cash_register_transferts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cashier_id');
            $table->unsignedBigInteger('cash_registers_id');
            $table->unsignedBigInteger('approver_id');
            $table->string('number');
            $table->string('fonds')->default(0);
            $table->string('solde')->default(0);
            $table->string('credits')->default(0);
            $table->boolean('statut')->default(0);
            $table->uuid('uuid')->unique()->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->boolean('is_synced')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('cash_register_transferts')) {
            Schema::table('cash_register_transferts', function (Blueprint $table) {
                if (Schema::hasTable('cash_registers')) {
                    $table->foreign('cash_registers_id')->references('id')->on('cash_registers')->onDelete('cascade')->onUpdate('cascade');
                }

                if (Schema::hasTable('users')) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
                    $table->foreign('cashier_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
                    $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('cash_register_transferts');
    }
};
