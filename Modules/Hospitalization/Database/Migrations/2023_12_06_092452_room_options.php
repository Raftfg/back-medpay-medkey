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
    public function up(): void
    {
        Schema::create('room_options',function(Blueprint $table){
            $table->bigIncrements('id');
            $table->string('name', 255)->nullable();
            $table->decimal('price', 13, 2);

            $table->unsignedBigInteger('user_id')->index()->nullable(); // Store the ID of the user that is executing an action on the resource.
            
            // Additional attributes
            $table->integer('is_synced')->default(0); // To know either the data is synchronized or not, defined as not synchronized by default.
            $table->uuid('uuid')->nullable()->unique(); // Store the UUID of the resource.
            $table->timestamp('deleted_at')->nullable(); // To apply soft delete.
            $table->timestamps();
        });

        if (Schema::hasTable('room_options') && Schema::hasTable('users')) {
            Schema::table('room_options', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null'); // Prevent the deletion of the line associated to a user when the user is deleted
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_options');
    }
};
