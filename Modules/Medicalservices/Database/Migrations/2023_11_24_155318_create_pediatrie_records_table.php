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
        Schema::create('pediatrie_records', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('services_id');
            $table->unsignedBigInteger('movments_id');

            $table->longText('reason')->nullable();
            $table->longText('complaint')->nullable();
            $table->longText('actions')->nullable();
            $table->longText('observation')->nullable();
            $table->longText('summary')->nullable();
            $table->longText('operator')->nullable();

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
        Schema::dropIfExists('pediatrie_records');
    }
};
