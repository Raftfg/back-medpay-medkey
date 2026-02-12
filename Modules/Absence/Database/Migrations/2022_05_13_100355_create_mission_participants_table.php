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

            $table->foreign('users_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('missions_id')->references('id')->on('missions')->restrictOnDelete();
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
