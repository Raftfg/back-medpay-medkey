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
        Schema::create('patient_insurances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->nullable();
            $table->unsignedBigInteger('patients_id');
            $table->unsignedBigInteger('pack_id')->nullable();
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            // $table->text('observation')->nullable();
            $table->string('numero_police', 20)->nullable(); // 20 est la longueur maximale, ajustez-la selon vos besoins
            $table->timestamps();
        });

        if (Schema::hasTable('patient_insurances')) {
            Schema::table('patient_insurances', function (Blueprint $table) {
                if (Schema::hasTable('patients')) {
                    $table->foreign('patients_id')
                        ->references('id')
                        ->on('patients')
                        ->onUpdate('cascade')
                        ->onDelete('cascade');
                }

                if (Schema::hasTable('packs')) {
                    $table->foreign('pack_id')
                        ->references('id')
                        ->on('packs')
                        ->onUpdate('cascade')
                        ->onDelete('cascade');
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
        Schema::dropIfExists('patient_insurances');
    }
};
