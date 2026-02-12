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
        Schema::create('urgences_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('services_id');
            $table->unsignedBigInteger('movments_id');
            $table->string('category');
            $table->string('level');
            $table->longText('description')->nullable();
            $table->longText('emergency_actions')->nullable();
            $table->longText('parent')->nullable();
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
        Schema::dropIfExists('urgences_records');
    }
};
