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
        Schema::create('patient_movement_details', function (Blueprint $table) {
            $table->id();
            $table->string('medical_acts_id');
            $table->unsignedBigInteger('movments_id');

            $table->double('medical_acts_qte');
            $table->integer('medical_acts_price');
            $table->boolean('paid')->default(0);
            $table->boolean('completed')->default(0);

            $table->string('type');
            $table->integer('services_id');


            // Clé étrangère vers la table movements
            $table->timestamps();
        });

        if (Schema::hasTable('patient_movement_details') && Schema::hasTable('movments')) {
            Schema::table('patient_movement_details', function (Blueprint $table) {
                $table->foreign('movments_id')
                    ->references('id')
                    ->on('movments')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
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
        Schema::dropIfExists('patient_movement_details');
    }
};
