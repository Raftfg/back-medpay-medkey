<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVacationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vacations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->nullable();
            $table->string('note')->nullable();
            $table->string('motif_urgence')->nullable();
            $table->unsignedBigInteger('departmentss_id')->nullable();
            $table->string('reject_reason')->nullable();
            $table->string('decision_chief')->nullable();
            $table->string('pathFile')->nullable();
            $table->unsignedBigInteger('users_id');
            $table->unsignedBigInteger('type_vacations_id');
            $table->timestamps();

            $table->uuid('uuid')->nullable()->unique(); //nullable parce que la migration est impossible

            $table->foreign('departmentss_id')->references('id')->on('departmentss')->restrictOnDelete();
            $table->foreign('users_id')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('type_vacations_id')->references('id')->on('type_vacations')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vacations');
    }
}
