<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTypeVacationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('type_vacations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code');
            $table->string('libelle');
            $table->string('require_certify');
            $table->timestamps();

            $table->uuid('uuid')->nullable()->unique(); //nullable parce que la migration est impossible
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('type_vacations');
    }
}
