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
        Schema::create('medical_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('movments_id');
            $table->unsignedBigInteger('service_movments_id');
            $table->text('path')->nullable();

            $table->foreign('movments_id')
                ->references('id')
                ->on('movments')
                ->onUpdate('cascade')
                ->onDelete('cascade'); // Option "on cascade"
            // Ajoutez d'autres colonnes si nécessaire

            $table->foreign('service_movments_id')
                ->references('id')
                ->on('service_movments')
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
        Schema::dropIfExists('medical_files');
    }
};
