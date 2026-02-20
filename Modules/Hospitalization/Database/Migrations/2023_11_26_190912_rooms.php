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
        Schema::create('rooms',function(Blueprint $table){
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedTinyInteger('bed_capacity');
            $table->string('description', 255)->nullable();
            $table->decimal('price', 13, 2);

            // Secondary keys
            $table->unsignedBigInteger('user_id')->index()->nullable(); // Store the ID of the user that is executing an action on the resource.
            
            // Additional attributes
            $table->integer('is_synced')->default(0); // To know either the data is synchronized or not, defined as not synchronized by default.
            $table->uuid('uuid')->nullable()->unique(); // Store the UUID of the resource.
            $table->timestamp('deleted_at')->nullable(); // To apply soft delete.
            $table->timestamps();
        });

        // En provisioning multi-module, la table users peut ne pas être prête.
        Schema::table('rooms', function (Blueprint $table) {
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
        Schema::dropIfExists('rooms');
    }
};
