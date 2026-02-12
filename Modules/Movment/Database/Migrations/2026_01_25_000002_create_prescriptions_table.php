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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            
            $table->unsignedBigInteger('patients_id');
            $table->foreign('patients_id')
                ->references('id')
                ->on('patients')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            $table->unsignedBigInteger('movments_id')->nullable();
            $table->foreign('movments_id')
                ->references('id')
                ->on('movments')
                ->onUpdate('cascade')
                ->onDelete('set null');
            
            $table->unsignedBigInteger('clinical_observation_id')->nullable();
            
            $table->unsignedBigInteger('doctor_id')->nullable()->comment('Médecin prescripteur');
            $table->date('prescription_date')->comment('Date de prescription');
            $table->text('notes')->nullable()->comment('Notes du prescripteur');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->date('valid_until')->nullable()->comment('Date de validité');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('prescriptions');
    }
};
