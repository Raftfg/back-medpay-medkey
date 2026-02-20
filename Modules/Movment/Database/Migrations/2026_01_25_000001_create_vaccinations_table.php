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
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            
            $table->unsignedBigInteger('patients_id');
            
            $table->unsignedBigInteger('movments_id')->nullable();
            
            $table->string('vaccine_name')->comment('Nom du vaccin');
            $table->string('vaccine_code')->nullable()->comment('Code du vaccin (ex: BCG, DTP, etc.)');
            $table->date('vaccination_date')->comment('Date de vaccination');
            $table->string('batch_number')->nullable()->comment('Numéro de lot');
            $table->string('administration_route')->nullable()->comment('Voie d\'administration');
            $table->string('site')->nullable()->comment('Site d\'injection');
            $table->text('notes')->nullable()->comment('Notes complémentaires');
            $table->unsignedBigInteger('doctor_id')->nullable()->comment('Médecin ayant administré');
            $table->date('next_dose_date')->nullable()->comment('Date de la prochaine dose');
            
            $table->timestamps();
            $table->softDeletes();
        });

        if (Schema::hasTable('vaccinations')) {
            Schema::table('vaccinations', function (Blueprint $table) {
                if (Schema::hasTable('patients')) {
                    $table->foreign('patients_id')
                        ->references('id')
                        ->on('patients')
                        ->onUpdate('cascade')
                        ->onDelete('cascade');
                }

                if (Schema::hasTable('movments')) {
                    $table->foreign('movments_id')
                        ->references('id')
                        ->on('movments')
                        ->onUpdate('cascade')
                        ->onDelete('set null');
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
        Schema::dropIfExists('vaccinations');
    }
};
