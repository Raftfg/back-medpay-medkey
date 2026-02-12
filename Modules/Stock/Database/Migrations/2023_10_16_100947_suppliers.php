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
        Schema::create('suppliers',function(Blueprint $table){
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email', 255)->unique()->nullable();
            $table->string('dial_code', 10);
            $table->unsignedBigInteger('phone_number')->unique();
            $table->string('address', 255)->nullable();
            $table->unsignedTinyInteger('profit_margin');

            //Additional attributes
            $table->unsignedBigInteger('user_id'); //Store the ID of the user that is executing an action on the resource.
            $table->integer('is_synced')->default(0); //To know either the data is synchronized or not, defined as not synchronized by default.
            $table->uuid('uuid')->nullable()->unique(); //Store the UUID of the resource.
            $table->timestamp('deleted_at')->nullable(); //To apply soft delete.
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
        Schema::dropIfExists('suppliers');
    }
};
