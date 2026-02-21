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
        Schema::create('dme_documents', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            
            $table->unsignedBigInteger('patients_id');
            
            $table->unsignedBigInteger('movments_id')->nullable();
            
            $table->unsignedBigInteger('clinical_observation_id')->nullable();
            
            $table->string('title')->comment('Titre du document');
            $table->enum('type', [
                'certificat_medical',
                'ordonnance',
                'resultat_examen',
                'compte_rendu',
                'imagerie',
                'laboratoire',
                'autre'
            ])->default('autre');
            $table->text('file_path')->comment('Chemin du fichier');
            $table->string('file_name')->comment('Nom du fichier');
            $table->string('mime_type')->nullable();
            $table->integer('file_size')->nullable()->comment('Taille en octets');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable()->comment('Utilisateur ayant uploadé');
            $table->date('document_date')->nullable()->comment('Date du document');
            
            $table->timestamps();
            $table->softDeletes();
        });

        if (Schema::hasTable('dme_documents')) {
            Schema::table('dme_documents', function (Blueprint $table) {
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
        Schema::dropIfExists('dme_documents');
    }
};
