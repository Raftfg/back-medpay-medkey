<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->string('projet')->nullable();
            $table->string('objet')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('place')->nullable();
            $table->mediumText('observation')->nullable();
            $table->string('reason')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('mission_head_id')->nullable();
            $table->unsignedBigInteger('departmentss_id')->nullable();
            $table->timestamps();

            $table->uuid('uuid')->nullable()->unique(); //nullable parce que la migration est impossible

            $table->foreign('departmentss_id')->references('id')->on('departmentss')->onDelete("cascade");
            $table->foreign('mission_head_id')->references('id')->on('users')->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('missions');
    }
}
