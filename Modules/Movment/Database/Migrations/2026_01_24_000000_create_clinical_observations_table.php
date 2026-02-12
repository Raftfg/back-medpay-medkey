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
        Schema::create('clinical_observations', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            
            // Relation patient
            $table->unsignedBigInteger('patients_id');
            $table->foreign('patients_id')
                ->references('id')
                ->on('patients')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            // Relation mouvement (optionnel - pour lier à une admission spécifique)
            $table->unsignedBigInteger('movments_id')->nullable();
            $table->foreign('movments_id')
                ->references('id')
                ->on('movments')
                ->onUpdate('cascade')
                ->onDelete('set null');
            
            // Médecin responsable (optionnel)
            $table->unsignedBigInteger('doctor_id')->nullable();
            
            // Données SOAP (Subjectif, Objectif, Analyse, Plan)
            $table->text('subjective')->nullable()->comment('Plaintes du patient');
            $table->text('objective')->nullable()->comment('Examen clinique');
            $table->text('assessment')->nullable()->comment('Diagnostic/Analyse');
            $table->text('plan')->nullable()->comment('Plan de traitement');
            
            // Signes vitaux
            $table->string('blood_pressure')->nullable()->comment('Tension artérielle');
            $table->string('heart_rate')->nullable()->comment('Fréquence cardiaque');
            $table->string('temperature')->nullable()->comment('Température');
            $table->string('respiratory_rate')->nullable()->comment('Fréquence respiratoire');
            $table->string('oxygen_saturation')->nullable()->comment('Saturation en oxygène');
            $table->string('weight')->nullable()->comment('Poids');
            $table->string('height')->nullable()->comment('Taille');
            
            // Métadonnées
            $table->dateTime('observation_date')->nullable()->comment('Date de l\'observation');
            $table->string('type')->default('consultation')->comment('Type: consultation, urgence, suivi, etc.');
            
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
        Schema::dropIfExists('clinical_observations');
    }
};
