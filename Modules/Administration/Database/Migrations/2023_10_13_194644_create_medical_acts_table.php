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
        Schema::create('medical_acts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('designation');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->unsignedBigInteger('services_id');
            $table->unsignedBigInteger('type_medical_acts_id');
            $table->timestamps();

            // Contrainte de clé étrangère pour services_id
            $table->foreign('services_id')
                ->references('id')
                ->on('services')
                ->onUpdate('cascade')
                ->onDelete('cascade'); // On cascade

            // Contrainte de clé étrangère pour type_medical_acts_id
            $table->foreign('type_medical_acts_id')
                ->references('id')
                ->on('type_medical_acts')
                ->onUpdate('cascade')
                ->onDelete('cascade'); // On cascade
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medical_acts');
    }
};
