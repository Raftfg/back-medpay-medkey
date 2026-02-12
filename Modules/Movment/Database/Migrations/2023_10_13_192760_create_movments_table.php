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
        Schema::create('movments', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->integer('iep');
            $table->double('ipp');
            $table->datetime('arrivaldate');
            $table->datetime('releasedate')->nullable();
            $table->string('incoming_reason')->nullable();
            $table->string('outgoing_reason')->nullable();

            $table->boolean('is_synced')->nullable();

            $table->unsignedBigInteger('patients_id');
            $table->string('active_services_code');

            $table->foreign('patients_id')
                ->references('id')
                ->on('patients')
                ->onUpdate('cascade')
                ->onDelete('cascade');

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
        Schema::dropIfExists('movments');
    }
};
