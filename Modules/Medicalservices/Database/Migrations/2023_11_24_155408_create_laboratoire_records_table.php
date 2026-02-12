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
        Schema::create('laboratoire_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('services_id');
            $table->unsignedBigInteger('movments_id');

            $table->string('act_code');
            $table->string('intrant');
            $table->string('param');
            $table->string('value');
            $table->string('ref_value');
            $table->string('unit');


            $table->longText('summary')->nullable();
            $table->string('operator')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('laboratoire_records');
    }
};
