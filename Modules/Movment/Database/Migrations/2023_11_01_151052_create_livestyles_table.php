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
        Schema::create('livestyles', function (Blueprint $table) {
            $table->id();

            $table->uuid()->unique();
            $table->string("name");
            $table->string("description")->nullable();
            $table->string("type")->nullable();

            $table->unsignedBigInteger("movments_id")->nullable();

            $table->unsignedBigInteger("patients_id");
            $table->foreign('patients_id')
                ->references('id')
                ->on('patients')
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
        Schema::dropIfExists('livestyles');
    }
};
