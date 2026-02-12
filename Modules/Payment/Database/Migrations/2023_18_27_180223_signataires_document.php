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
        Schema::create('signataires_document', function (Blueprint $table) {

            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->boolean('is_synced')->default(0);
            $table->string('reference', 255);
            $table->unsignedBigInteger('signataires_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('centre_id')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('signataires_id')->references('id')->on('signataires')->onDelete('cascade')->onUpdate('cascade');

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
        Schema::dropIfExists('signataires_document');
    }
};
