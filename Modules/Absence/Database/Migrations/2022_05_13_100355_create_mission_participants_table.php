<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mission_participants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('institutions')->nullable();
            $table->unsignedBigInteger('missions_id')->nullable();
            $table->unsignedBigInteger('users_id')->nullable();
            $table->timestamps();

            $table->uuid('uuid')->nullable()->unique(); //nullable parce que la migration est impossible
        });

        // En provisioning multi-tenant, l'ordre des migrations modules peut varier.
        // On ajoute les contraintes uniquement si les tables cibles existent.
        Schema::table('mission_participants', function (Blueprint $table) {
            if (Schema::hasTable('users')) {
                $table->foreign('users_id')->references('id')->on('users')->restrictOnDelete();
            }

            if (Schema::hasTable('missions')) {
                $table->foreign('missions_id')->references('id')->on('missions')->restrictOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mission_participants');
    }
}
