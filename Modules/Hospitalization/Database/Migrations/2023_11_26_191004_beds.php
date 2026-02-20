<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('beds',function(Blueprint $table){
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->string('name')->unique();
            $table->enum('state', ['busy', 'free'])->default('free');

            // Secondary keys
            $table->unsignedBigInteger('room_id')->index();
            $table->unsignedBigInteger('patient_id')->index()->nullable()->default(null);
            $table->unsignedBigInteger('user_id')->index()->nullable()->default(null); // Store the ID of the user that is executing an action on the resource.

            // Additional attributes
            $table->integer('is_synced')->default(0); // To know either the data is synchronized or not, defined as not synchronized by default.
            $table->uuid('uuid')->nullable()->unique(); // Store the UUID of the resource.
            $table->timestamp('deleted_at')->nullable(); // To apply soft delete.
            $table->timestamps();
        });

        // En provisioning multi-module, l'ordre de création peut varier.
        Schema::table('beds', function (Blueprint $table) {
            if (Schema::hasTable('rooms')) {
                $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            }
            if (Schema::hasTable('patients')) {
                $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
            }
            if (Schema::hasTable('users')) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('beds');
    }
};
