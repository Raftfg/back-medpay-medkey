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
        Schema::create('service_movments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('services_id');
            $table->unsignedBigInteger('movments_id');
            $table->longText('measurement')->nullable();
            $table->longText('complaint')->nullable();
            $table->longText('exam')->nullable();
            $table->longText('observation')->nullable();
            $table->longText('summary')->nullable();
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
        Schema::dropIfExists('service_movments');
    }
};
