<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('bed_patients',function(Blueprint $table){
            $table->bigIncrements('id');
            $table->string('comment', 255)->nullable();
            $table->timestamp('start_occupation_date');
            $table->timestamp('end_occupation_date')->default(DB::raw('CURRENT_TIMESTAMP'));;

            // Secondary keys
            $table->unsignedBigInteger('bed_id')->index();
            $table->unsignedBigInteger('patient_id')->index();
            $table->unsignedBigInteger('user_id')->index()->nullable(); // Store the ID of the user that is executing an action on the resource.

            $table->foreign('bed_id')->references('id')->on('beds')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null'); // Prevent the deletion of the line associated to a user when the user is deleted
            
            // Additional attributes
            $table->integer('is_synced')->default(0); // To know either the data is synchronized or not, defined as not synchronized by default.
            $table->uuid('uuid')->nullable()->unique(); // Store the UUID of the resource.
            $table->timestamp('deleted_at')->nullable(); // To apply soft delete.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bed_patients');
    }
};
