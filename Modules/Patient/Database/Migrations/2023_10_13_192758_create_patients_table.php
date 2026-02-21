<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ipp')->unique()->nullable();
            $table->uuid('uuid')->unique()->nullable();
            $table->unsignedBigInteger('users_id')->nullable();
            $table->boolean('is_synced')->default(0);
            $table->timestamp('deleted_at')->nullable();
            $table->string('lastname')->nullable();
            $table->string('firstname')->nullable();
            $table->date('date_birth')->nullable();
            $table->integer('age')->nullable();
            $table->string('maison')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('profession')->nullable();
            $table->string('gender')->nullable();
            $table->string('emergency_contac')->nullable();
            $table->string('marital_status')->nullable();
            $table->text('autre')->nullable();
            $table->date('date_deces')->nullable();
            $table->string('nom_marital')->nullable();
            $table->string('code_postal')->nullable();
            $table->string('nom_pere')->nullable();
            $table->string('nom_mere')->nullable();
            $table->string('phone_pere')->nullable();
            $table->string('phone_mere')->nullable();
            $table->string('quartier')->nullable();
            $table->unsignedBigInteger('pays_id')->nullable();
            $table->unsignedBigInteger('departements_id')->nullable();
            $table->unsignedBigInteger('communes_id')->nullable();
            $table->unsignedBigInteger('arrondissements_id')->nullable();

            $table->timestamps();
        });

        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                if (Schema::hasTable('pays')) {
                    $table->foreign('pays_id')
                        ->references('id')
                        ->on('pays')
                        ->onUpdate('cascade')
                        ->onDelete('cascade');
                }

                if (Schema::hasTable('departements')) {
                    $table->foreign('departements_id')
                        ->references('id')
                        ->on('departements')
                        ->onUpdate('cascade')
                        ->onDelete('cascade');
                }

                if (Schema::hasTable('communes')) {
                    $table->foreign('communes_id')
                        ->references('id')
                        ->on('communes')
                        ->onUpdate('cascade')
                        ->onDelete('cascade');
                }

                if (Schema::hasTable('arrondissements')) {
                    $table->foreign('arrondissements_id')
                        ->references('id')
                        ->on('arrondissements')
                        ->onUpdate('cascade')
                        ->onDelete('cascade');
                }

                if (Schema::hasTable('users')) {
                    $table->foreign('users_id')
                        ->references('id')
                        ->on('users')
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
        Schema::dropIfExists('patients');
    }
}