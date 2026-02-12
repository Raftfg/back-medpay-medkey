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
        //
        Schema::create('factures', function (Blueprint $table) {

            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->unsignedBigInteger('cash_registers_id')->nullable();
            $table->unsignedBigInteger('movments_id');
            $table->integer('mode_payements_id')->nullable();
            $table->boolean('is_synced');
            $table->string('reference', 255);
            $table->string('acte_medical_id')->nullable();
            $table->string('lots_uuid')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('centre_id')->nullable();
            $table->string('code', 125);
            $table->string('designation', 255);
            $table->string('type', 125);
            $table->decimal('partial_amount', 10,2)->nullable();
            $table->decimal('prix', 10,2);
            $table->integer('quantite');
            $table->decimal('amount', 10,2);
            $table->integer('paid')->default(0);
            $table->boolean('is_factured')->default(0)->nullable();
            $table->integer('percentageassurance')->nullable();
            $table->timestamps();
            $table->foreign('movments_id')->references('id')->on('movments')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            //decimal('percentage', 8, 2); $table->foreign('cash_registers_id')->references('id')->on('cash_registers')->onDelete('cascade')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('factures');
    }
};
