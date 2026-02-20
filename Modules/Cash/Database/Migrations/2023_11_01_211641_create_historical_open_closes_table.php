<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\CashRegister; 


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historical_open_closes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cashier_id');
            $table->unsignedBigInteger('cash_registers_id');
            $table->decimal('solde')->default(0)->nullable();
            $table->decimal('credits')->default(0)->nullable();
            $table->decimal('total_partial')->default(0)->nullable();
            $table->decimal('total_espece')->default(0)->nullable();
            $table->decimal('totalMtnMomo')->default(0)->nullable();
            $table->decimal('totalMoovMomo')->default(0)->nullable();
            $table->decimal('totalCeltis')->default(0)->nullable();
            $table->decimal('totalCarteBancaire')->default(0)->nullable();
            $table->decimal('totalCarteCredit')->default(0)->nullable();
            $table->decimal('totalTresorPay')->default(0)->nullable();
            $table->boolean('statut')->default(1);
            $table->uuid('uuid')->unique()->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->boolean('is_synced')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('historical_open_closes')) {
            Schema::table('historical_open_closes', function (Blueprint $table) {
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
        Schema::dropIfExists('historical_open_closes');
    }
};
